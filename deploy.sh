#!/bin/bash

set -e

USAGE="deploy.sh [host] [docroot] [config]"

if [ "$#" -ne 3 ]; then
  echo "$USAGE"
  exit 1
fi

HOST=$1
DOCROOT=$2
CONFIG=$3

ssh $HOST "mkdir -p $DOCROOT/cache"

rsync -rv ./routes "${HOST}:${DOCROOT}"
rsync -rv ./src "${HOST}:${DOCROOT}"
rsync -rv ./templates "${HOST}:${DOCROOT}"
rsync -rv --include="bootstrap.php" --include="cli-config.php" --include="index.php" --include="composer.*" --exclude="*" ./* "${HOST}:${DOCROOT}/"
rsync -v "./config/config.${CONFIG}.yml" "${HOST}:${DOCROOT}/config.yml"
rsync -v --ignore-existing "./credentials.yml.EXAMPLE" "${HOST}:${DOCROOT}/credentials.yml"

ssh $HOST "cd $DOCROOT && composer install --no-dev --optimize-autoloader && vendor/bin/doctrine orm:generate-proxies"

exit 0
