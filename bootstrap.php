<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

require_once "vendor/autoload.php";

$app = new \PF\Slim\PinfinderApp(
  array(
    'mode' => 'development',
    'view' => new \Slim\Views\Twig(),
  )
);

$config = Setup::createYAMLMetadataConfiguration(array(__DIR__ . '/src/PF/Doctrine'), $app->getMode() === 'development', null, null);

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

$registry = new \PF\Serializer\SimpleManagerRegistry(
  function($id) use($entityManager) {
    switch ($id) {
      case 'default_manager':
        return $entityManager;

      default:
        throw new \RuntimeException(sprintf('Unknown service id "%s".', $id));
    }
  }
);

$fallback_constructor = new \JMS\Serializer\Construction\UnserializeObjectConstructor();

$object_constructor = new \JMS\Serializer\Construction\DoctrineObjectConstructor($registry, $fallback_constructor);

$initialized_object_constructor = new \PF\Serializer\InitializedObjectConstructor($object_constructor);

$serializer_builder = JMS\Serializer\SerializerBuilder::create()
  ->setMetadataDirs(array('PF' => __DIR__ . '/src/PF/Serializer'))
  ->setDebug($app->getMode() === 'development')
  ->setObjectConstructor($initialized_object_constructor);


$app->configureMode('development', function () use ($app) {
  $app->config(array(
    'cookies.lifetime' => 'Never',
    'debug' => true,
  ));
});

$app->configureMode('production', function () use ($app, $config, $serializer_builder) {
  $app->config(array(
    'cookies.lifetime' => '2 Hours',
    'debug' => false,
  ));

  $config->setQueryCacheImpl(new \Doctrine\Common\Cache\ApcCache());
  $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ApcCache());

  $serializer_builder->setCacheDir('/tmp');
});

$serializer = $serializer_builder->build();

$app->add(new \PF\Slim\ResponseMiddleware($serializer));

$venueDeserializer = new \PF\Serializer\VenueDeserializer();
$venueDeserializer->setEntityManager($entityManager);
$venueDeserializer->setSerializer($serializer);

$app->view()->parserOptions = array(
  'autoescape' => false,
);

$app->notFound(function () use ($app) {
  $app->status(401);
  $app->render('404.html');
});
