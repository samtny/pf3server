#!/bin/bash

set -e

if [ ! -f "composer.phar" ]; then
  wget -O composer-setup.php https://getcomposer.org/installer
  php -r "if (hash_file('SHA384', 'composer-setup.php') === '7228c001f88bee97506740ef0888240bd8a760b046ee16db8f4095c0d8d525f2367663f22a46b48d072c816e7fe19959') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
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
