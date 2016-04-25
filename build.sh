#!/bin/bash

set -e

USAGE="build.sh -dc [config]"

DEPS=false
CLEAN=false

while getopts "dc" opt; do
    case "$opt" in
        d)
            DEPS=true
            ;;
        c)
            CLEAN=true
            ;;
    esac
done
shift "$((OPTIND-1))"

if [ "$#" -lt 1 ]; then
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

HOST=$config_pf3server_deploy_host
USER=$config_pf3server_deploy_user
DOCROOT=$config_pf3server_docroot

BUILD_DIR="./build"

if [ "$CLEAN" = true ]; then
  [ -d ${BUILD_DIR} ] && rm -rf ${BUILD_DIR}
fi

mkdir -p ${BUILD_DIR}

cp -ru routes/ src/ templates/ bootstrap.php cli-config.php index.php composer.* .htaccess credentials.yml.EXAMPLE -t ${BUILD_DIR}
cp -u ${CONFIG_FILE} ${BUILD_DIR}/config.yml

mkdir -p $BUILD_DIR/cache

[ "$DEPS" = false ] && exit 0

if [ ! -f "composer.phar" ]; then
  wget -O composer-setup.php https://getcomposer.org/installer
  php -r "if (hash_file('SHA384', 'composer-setup.php') === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  php -d allow_url_fopen=On composer-setup.php
  php -r "unlink('composer-setup.php');"
fi

if [ "$config_pf3server_runmode" == "production" ]; then
  php -d allow_url_fopen=On composer.phar install --working-dir=${BUILD_DIR} --no-dev --optimize-autoloader
  cd ${BUILD_DIR} && vendor/bin/doctrine orm:generate-proxies
  cd -
else
  php -d allow_url_fopen=On composer.phar install --working-dir=${BUILD_DIR}
fi

exit 0
