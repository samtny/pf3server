<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

use PF\Serializer\PinfinderSerializer;
use PF\Slim\PinfinderApp;

use Slim\Views\Twig;

require_once "vendor/autoload.php";

$app = new PinfinderApp(
  array(
    'mode' => 'development',
    'view' => new Twig(),
  )
);

$config = Setup::createYAMLMetadataConfiguration(array(__DIR__ . '/src/PF/Doctrine/yml'), $app->getMode() === 'development', null, null);

$config->addCustomNumericFunction('sin', '\DoctrineExtensions\Query\Mysql\Sin');
$config->addCustomNumericFunction('cos', '\DoctrineExtensions\Query\Mysql\Cos');
$config->addCustomNumericFunction('acos', '\DoctrineExtensions\Query\Mysql\Acos');
$config->addCustomNumericFunction('radians', '\DoctrineExtensions\Query\Mysql\Radians');

$conn = array(
  'driver' => 'pdo_mysql',
  'dbname' => 'pf3server',
  'user' => 'pf3server',
  'password' => 'pf3server',
  'host' => 'localhost',
);

$entityManager = EntityManager::create($conn, $config);

$app->configureMode('development', function () use ($app) {
  $app->config(array(
    'cookies.lifetime' => 'Never',
    'debug' => true,
  ));
});

$app->configureMode('production', function () use ($app, $config) {
  $app->config(array(
    'cookies.lifetime' => '2 Hours',
    'debug' => false,
  ));

  $config->setQueryCacheImpl(new \Doctrine\Common\Cache\ApcCache());
  $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ApcCache());
});

$serializer = PinfinderSerializer::create($entityManager, $app->getMode() === 'development');

$app->add(new \PF\Slim\ResponseMiddleware($serializer));

$app->view()->parserOptions = array(
  'autoescape' => false,
);

$app->notFound(function () use ($app) {
  $app->status(401);
  $app->render('404.html');
});
