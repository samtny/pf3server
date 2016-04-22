#!/bin/bash

set -e

USAGE="test.sh [config]"

if [ "$#" -ne 1 ]; then
  echo "$USAGE"
  exit 1
fi

CONFIG=$1

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

HOST=$config_pf3server_url
USER=$config_pf3server_deploy_user
DOCROOT=$config_pf3server_docroot

rsync -rv ./tests "${USER}@${HOST}:${DOCROOT}"
rsync -rv "phpunit.xml" "${USER}@${HOST}:${DOCROOT}/"

ssh ${USER}@${HOST} "php -d allow_url_fopen=On composer.phar install --working-dir=${DOCROOT} && ${DOCROOT}/vendor/bin/phpunit --configuration ${DOCROOT}/phpunit.xml"

exit 0
