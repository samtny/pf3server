<?php

require_once 'bootstrap.php';

use PF\Middleware\PinfinderResponseMiddleware;
use PF\Serializer\PinfinderSerializer;

$entityManager = Bootstrap::getEntityManager();

$config = Bootstrap::getConfig();

$logger = Bootstrap::getLogger();

$container = [
  'settings' => [
    'displayErrorDetails' => true,
    'debug' => true,
    'determineRouteBeforeAppMiddleware' => true
  ],
  'response' => function () {
    return new \PF\Slim\PinfinderResponse();
  }
];

$app = new \Slim\App($container);

$serializer = PinfinderSerializer::create($entityManager, Bootstrap::getRunmode() === 'production');

$app->add(new PinfinderResponseMiddleware($serializer));

/*

if (Bootstrap::getRunmode() === 'profile') {
  $app->add(new \PF\Middleware\XHProfMiddleware());
}

 */

require 'routes/legacy.php';
require 'routes/venue.php';

/*

require 'routes/login.php';

require 'routes/comment.php';
require 'routes/game.php';
require 'routes/stats.php';
require 'routes/geocode.php';
require 'routes/notification.php';
require 'routes/machine.php';

require 'routes/user.php';
require 'routes/app.php';

*/

$app->run();
