#!/bin/bash

set -e

if [ ! -f "composer.phar" ]; then
  wget -O composer-setup.php https://getcomposer.org/installer
  php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  php -d allow_url_fopen=On composer-setup.php
  php -r "unlink('composer-setup.php');"
fi

if [ "$config_pf3server_runmode" == "production" ]; then
  php -d allow_url_fopen=On composer.phar install --no-dev --optimize-autoloader
  vendor/bin/doctrine orm:generate-proxies
else
  php -d allow_url_fopen=On composer.phar install
fi

exit 0
