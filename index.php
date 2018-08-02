<?php

require_once 'bootstrap.php';

use PF\Middleware\PinfinderResponseMiddleware;
use PF\Serializer\PinfinderSerializer;

$entityManager = Bootstrap::getEntityManager();

$config = Bootstrap::getConfig();

$logger = Bootstrap::getLogger();

$app = new \Slim\App(
  [
    'settings' => [
      'displayErrorDetails' => true,
      'debug' => true,
    ],
    'response' => function () {
      return new \PF\Slim\PinfinderResponse();
    }
  ]
);

/*

if (Bootstrap::getRunmode() === 'profile') {
  $app->add(new \PF\Middleware\XHProfMiddleware());
}

$adminRouteMiddleware = new \PF\Middleware\AdminRouteMiddleware();

$requestStatsMiddleware = new \PF\Middleware\RequestStatsMiddleware();




require 'routes/login.php';
require 'routes/venue.php';
require 'routes/comment.php';
require 'routes/game.php';
require 'routes/stats.php';
require 'routes/geocode.php';
require 'routes/notification.php';
require 'routes/machine.php';
require 'routes/legacy.php';
require 'routes/user.php';
require 'routes/app.php';
*/

$serializer = PinfinderSerializer::create($entityManager, Bootstrap::getRunmode() === 'production');

$app->add(new PinfinderResponseMiddleware($serializer));

require 'routes/venue.php';

$app->run();
