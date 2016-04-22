#!/bin/bash

set -e

USAGE="deploy.sh [config]"

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

HOST=$config_pf3server_deploy_host
USER=$config_pf3server_deploy_user
DOCROOT=$config_pf3server_docroot

ssh ${USER}@${HOST} "mkdir -p $DOCROOT/cache"

rsync -rv ./routes "${USER}@${HOST}:${DOCROOT}"
rsync -rv ./src "${USER}@${HOST}:${DOCROOT}"
rsync -rv ./templates "${USER}@${HOST}:${DOCROOT}"
rsync -rv --include=".htaccess" --include="bootstrap.php" --include="cli-config.php" --include="index.php" --include="composer.*" --exclude="*" . "${USER}@${HOST}:${DOCROOT}/"
rsync -v ${CONFIG_FILE} "${USER}@${HOST}:${DOCROOT}/config.yml"
rsync -v --ignore-existing "./credentials.yml.EXAMPLE" "${USER}@${HOST}:${DOCROOT}/credentials.yml"

ssh ${USER}@${HOST} "mkdir -p ~/bin"

ssh ${USER}@${HOST} "wget -O composer-setup.php https://getcomposer.org/installer"
ssh ${USER}@${HOST} "php -r \"if (hash_file('SHA384', 'composer-setup.php') === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;\""
ssh ${USER}@${HOST} "php -d allow_url_fopen=On composer-setup.php"
ssh ${USER}@${HOST} "php -r \"unlink('composer-setup.php');\""

ssh ${USER}@${HOST} "php -d allow_url_fopen=On composer.phar install --working-dir=${DOCROOT} --no-dev --optimize-autoloader"
ssh ${USER}@${HOST} "cd ${DOCROOT} && vendor/bin/doctrine orm:generate-proxies"

exit 0
