#!/bin/bash

set -e

USAGE="build.sh [config]"

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

if [ -f "/usr/local/bin/php7" ]; then
  php="/usr/local/bin/php7"
else
  php="php"
fi

if [ ! -f "composer.phar" ]; then
  wget -O composer-setup.php https://getcomposer.org/installer
  ${php} -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  ${php} -d allow_url_fopen=On composer-setup.php
  ${php} -r "unlink('composer-setup.php');"
fi

if [ "$config_pf3server_runmode" == "production" ]; then
  echo -e "Installing PRODUCTION dependencies"
  ${php} -d allow_url_fopen=On composer.phar install --no-dev --optimize-autoloader
  vendor/bin/doctrine orm:generate-proxies
else
  echo -e "Installing DEVELOPMENT dependencies"
  ${php} -d allow_url_fopen=On composer.phar install
fi

exit 0
