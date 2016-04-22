#!/bin/bash

set -e

USAGE="deploy.sh [host] [docroot] [runmode]"

if [ "$#" -ne 3 ]; then
  echo "$USAGE"
  exit 1
fi

HOST=$1
DOCROOT=$2
RUNMODE=$3

rsync -rv --include="routes" --include="src" --include="templates" --include="bootstrap.php" --include="index.php" --include="composer.*" --exclude="*" ./* $HOST:$DOCROOT/

ssh $HOST "cd $DOCROOT && composer install --no-dev --optimize-autoloader"

exit 0
