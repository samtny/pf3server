<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

require_once "vendor/autoload.php";

define('PF_DEBUG', true);

$conn = array(
  'driver' => 'pdo_mysql',
  'dbname' => 'pf3server',
  'user' => 'pf3server',
  'password' => 'pf3server',
  'host' => 'localhost',
);

$config = Setup::createYAMLMetadataConfiguration(array(__DIR__ . '/src/PF/Doctrine/yml'), PF_DEBUG === true, null, null);
$config->addCustomNumericFunction('SIN', '\DoctrineExtensions\Query\Mysql\Sin');
$config->addCustomNumericFunction('COS', '\DoctrineExtensions\Query\Mysql\Cos');
$config->addCustomNumericFunction('ACOS', '\DoctrineExtensions\Query\Mysql\Acos');
$config->addCustomNumericFunction('RADIANS', '\DoctrineExtensions\Query\Mysql\Radians');
$config->addCustomNumericFunction('YEAR', '\DoctrineExtensions\Query\Mysql\Year');
$config->addCustomNumericFunction('MONTH', '\DoctrineExtensions\Query\Mysql\Month');
$config->addCustomNumericFunction('DATEDIFF', '\DoctrineExtensions\Query\Mysql\DateDiff');
$config->addCustomStringFunction('DATE_FORMAT', '\DoctrineExtensions\Query\Mysql\DateFormat');

if (PF_DEBUG) {
  $config->setQueryCacheImpl(new \Doctrine\Common\Cache\ApcCache());
  $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ApcCache());
}

$entityManager = EntityManager::create($conn, $config);
