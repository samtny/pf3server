#!/bin/bash

set -e

USAGE="test.sh [config] [target]"

if [ "$#" -lt 1 ]; then
  echo "$USAGE"
  exit 1
fi

CONFIG=$1
TARGET=$2

parse_yaml() {
   local prefix=$2
   local s='[[:space:]]*' w='[a-zA-Z0-9_]*' fs=$(echo @|tr @ '\034')
   sed -ne "s|^\($s\)\($w\)$s:$s\"\(.*\)\"$s\$|\1$fs\2$fs\3|p" \
        -e "s|^\($s\)\($w\)$s:$s\(.*\)$s\$|\1$fs\2$fs\3|p"  $1 |
   awk -F$fs '{
      indent = length($1)/2;
      vname[indent] = $2;
      for (i in vname) {if (i > indent) {delete vname[i]}}
      if (length($3) > 0) {
         vn=""; for (i=0; i<indent; i++) {vn=(vn)(vname[i])("_")}
         printf("%s%s%s=\"%s\"\n", "'$prefix'",vn, $2, $3);
      }
   }'
}

CONFIG_FILE="./config/config.${CONFIG}.yml"

eval $(parse_yaml ${CONFIG_FILE} "config_")

HOST=$config_pf3server_deploy_host
USER=$config_pf3server_deploy_user
DOCROOT=$config_pf3server_docroot

rsync -rv ./migrate "${USER}@${HOST}:${DOCROOT}"

if [ "$TARGET" != "" ]; then
  ssh ${USER}@${HOST} "php -d allow_url_fopen=On ${DOCROOT}/migrate/migrate_${TARGET}.php"
else
  if [ ! -f "./migrate/ipdb.html" ]; then
    echo "ipdb.html does not exist, aborting"
    exit 1
  fi
  if [ ! -f "./migrate/gamedict.txt" ]; then
    echo "gamedict.txt does not exist, aborting"
    exit 1
  fi
  ssh ${USER}@${HOST} "php -d allow_url_fopen=On ${DOCROOT}/migrate/migrate_games.php"
  ssh ${USER}@${HOST} "php -d allow_url_fopen=On ${DOCROOT}/migrate/migrate_venues.php"
  if [ ! -f "./migrate/tokens.txt" ]; then
    echo "tokens.txt does not exist, aborting"
    exit 1
  fi
  ssh ${USER}@${HOST} "php -d allow_url_fopen=On ${DOCROOT}/migrate/migrate_tokens.php"
fi

exit 0
