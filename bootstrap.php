<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

\Doctrine\DBAL\Types\Type::addType('point', '\CrEOF\Spatial\DBAL\Types\Geometry\PointType');

$app = new \Slim\Slim(
  array(
    'mode' => 'development',
    'view' => new \Slim\Views\Twig(),
  )
);

$app->view()->parserOptions = array(
  'autoescape' => false,
);

$app->notFound(function () use ($app) {
  $app->render('404.html');
});

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

$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $app->getMode() === 'development');

$conn = array(
  'driver' => 'pdo_mysql',
  'dbname' => 'pf3server',
  'user' => 'pf3server',
  'password' => 'pf3server',
  'host' => 'localhost',
);

$entityManager = EntityManager::create($conn, $config);
