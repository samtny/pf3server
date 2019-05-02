<?php

require_once 'bootstrap.php';

use PF\Middleware\PinfinderResponseMiddleware;
use PF\Serializer\PinfinderSerializer;

$entityManager = Bootstrap::getEntityManager();

$config = Bootstrap::getConfig();

$logger = Bootstrap::getLogger();

$runmode = Bootstrap::getRunmode();

$container = [
  'settings' => [
    'displayErrorDetails' => $runmode === 'development',
    'debug' => $runmode === 'development',
    'determineRouteBeforeAppMiddleware' => true
  ],
  'response' => function () {
    return new \PF\Slim\PinfinderResponse();
  }
];

$app = new \Slim\App($container);

$serializer = PinfinderSerializer::create($entityManager, $runmode === 'production');

$app->add(new PinfinderResponseMiddleware($serializer));

if ($runmode === 'profile') {
  $app->add(new \PF\Middleware\PinfinderXHProfMiddleware());
}

require 'routes/app.php';
require 'routes/comment.php';
require 'routes/game.php';
require 'routes/geocode.php';
require 'routes/legacy.php';
require 'routes/login.php';
require 'routes/machine.php';
require 'routes/notification.php';
require 'routes/stats.php';
require 'routes/user.php';
require 'routes/venue.php';

$app->run();
