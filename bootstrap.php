<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use Doctrine\Common\Annotations\AnnotationRegistry;

require_once "vendor/autoload.php";

require_once "src/DoubleMetaPhone.php";

AnnotationRegistry::registerAutoloadNamespace('JMS\Serializer\Annotation', __DIR__ . "/vendor/jms/serializer/src");

$app = new \PF\PinfinderApp(
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

$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $app->getMode() === 'development', null, null, false);

$config->addCustomNumericFunction('sin', '\DoctrineExtensions\Query\Mysql\Sin');
$config->addCustomNumericFunction('cos', '\DoctrineExtensions\Query\Mysql\Cos');
$config->addCustomNumericFunction('acos', '\DoctrineExtensions\Query\Mysql\Acos');
$config->addCustomNumericFunction('radians', '\DoctrineExtensions\Query\Mysql\Radians');

//$config->setQueryCacheImpl(new \Doctrine\Common\Cache\ApcCache());
//$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ApcCache());

$conn = array(
  'driver' => 'pdo_mysql',
  'dbname' => 'pf3server',
  'user' => 'pf3server',
  'password' => 'pf3server',
  'host' => 'localhost',
);

$entityManager = EntityManager::create($conn, $config);

$connection = $entityManager->getConnection();

$registry = new \PF\SimpleManagerRegistry(
  function($id) use($connection, $entityManager) {
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

$initialized_object_constructor = new \PF\InitializedObjectConstructor($object_constructor);

//$serializer = JMS\Serializer\SerializerBuilder::create()->setCacheDir('/tmp')->setDebug($app->getMode() === 'development')->build();
//$serializer = JMS\Serializer\SerializerBuilder::create()->setDebug($app->getMode() === 'development')->build();
$serializer = JMS\Serializer\SerializerBuilder::create()
  ->setMetadataDirs(array('PF' => __DIR__ . '/src'))
  ->setDebug($app->getMode() === 'development')
  ->setObjectConstructor($initialized_object_constructor)
  //->setCacheDir('/tmp')
  ->build();
//$serializer = JMS\Serializer\SerializerBuilder::create()->setDebug($app->getMode() === 'development')->setObjectConstructor($object_constructor)->build();

$app->add(new \PF\ResponseMiddleware($serializer));
