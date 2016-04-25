<?php

require_once 'bootstrap.php';

use PF\Serializer\PinfinderSerializer;
use PF\Slim\PinfinderApp;
use Slim\Views\Twig;

$entityManager = Bootstrap::getEntityManager();

$app = new PinfinderApp(
  array(
    'mode' => Bootstrap::getRunmode(),
    'view' => new Twig(),
  )
);

$app->configureMode('development', function () use ($app) {
  $app->config(array(
    'cookies.lifetime' => 'Never',
    'debug' => true,
  ));
});

$app->configureMode('production', function () use ($app) {
  $app->config(array(
    'cookies.lifetime' => '2 Hours',
    'debug' => false,
  ));
});

$app->view()->parserOptions = array(
  'autoescape' => false,
);

$app->notFound(function () use ($app) {
  $app->status(401);
  $app->render('404.html');
});

$serializer = PinfinderSerializer::create($entityManager, Bootstrap::getRunmode() === 'production');

if (Bootstrap::getRunmode() === 'profile') {
  $app->add(new \PF\Slim\XHProfMiddleware());
}

$app->add(new \PF\Slim\ResponseMiddleware($serializer));

$adminRouteMiddleware = new \PF\Slim\AdminRouteMiddleware();

require 'routes/login.php';
require 'routes/admin.php';
require 'routes/venue.php';
require 'routes/comment.php';
require 'routes/game.php';
require 'routes/stats.php';
require 'routes/geocode.php';
require 'routes/notification.php';
require 'routes/machine.php';
require 'routes/legacy.php';
require 'routes/user.php';

$app->run();
