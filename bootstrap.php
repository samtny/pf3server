<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Yaml\Parser;

require_once "vendor/autoload.php";

$parser = new Parser();

$runmode = $parser->parse(file_get_contents(__DIR__ . '/config.yml'))['pf3server_runmode'];

$conn = array(
  'driver' => 'pdo_mysql',
  'dbname' => 'pf3server',
  'user' => 'pf3server',
  'password' => 'pf3server',
  'host' => 'localhost',
);

$cache_impl = $runmode === 'production' ? new \Doctrine\Common\Cache\ApcCache() : null;

$config = Setup::createYAMLMetadataConfiguration(array(__DIR__ . '/src/PF/Doctrine/yml'), $runmode === 'development', null, $cache_impl);
$config->addCustomNumericFunction('SIN', '\DoctrineExtensions\Query\Mysql\Sin');
$config->addCustomNumericFunction('COS', '\DoctrineExtensions\Query\Mysql\Cos');
$config->addCustomNumericFunction('ACOS', '\DoctrineExtensions\Query\Mysql\Acos');
$config->addCustomNumericFunction('RADIANS', '\DoctrineExtensions\Query\Mysql\Radians');
$config->addCustomNumericFunction('YEAR', '\DoctrineExtensions\Query\Mysql\Year');
$config->addCustomNumericFunction('MONTH', '\DoctrineExtensions\Query\Mysql\Month');
$config->addCustomNumericFunction('DATEDIFF', '\DoctrineExtensions\Query\Mysql\DateDiff');
$config->addCustomStringFunction('DATE_FORMAT', '\DoctrineExtensions\Query\Mysql\DateFormat');
$config->addCustomDatetimeFunction('LAST_DAY', '\DoctrineExtensions\Query\Mysql\LastDay');

$entityManager = EntityManager::create($conn, $config);
