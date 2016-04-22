<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Yaml\Parser;

require_once "vendor/autoload.php";

class Bootstrap {
  private static $entityManager;

  private static $config;
  private static $runmode;

  public static function go() {
    $parser = new Parser();

    $config = $parser->parse(file_get_contents(__DIR__ . '/config.yml'));

    self::$config = $config;

    self::$runmode = $config['pf3server_runmode'];

    $credentials = $parser->parse(file_get_contents(__DIR__ . '/credentials.yml'));

    $conn = array(
      'driver' => 'pdo_mysql',
      'dbname' => $credentials['pf3server_db_name'],
      'user' => $credentials['pf3server_db_user'],
      'password' => $credentials['pf3server_db_password'],
      'host' => $credentials['pf3server_db_host'],
    );

    $proxy_dir = __DIR__ . '/cache';

    $cache_impl = self::$runmode === 'production' ? new \Doctrine\Common\Cache\ApcCache() : null;

    $config = Setup::createYAMLMetadataConfiguration(array(__DIR__ . '/src/PF/Doctrine/yml'), self::$runmode === 'development', $proxy_dir, $cache_impl);
    $config->addCustomNumericFunction('SIN', '\DoctrineExtensions\Query\Mysql\Sin');
    $config->addCustomNumericFunction('COS', '\DoctrineExtensions\Query\Mysql\Cos');
    $config->addCustomNumericFunction('ACOS', '\DoctrineExtensions\Query\Mysql\Acos');
    $config->addCustomNumericFunction('RADIANS', '\DoctrineExtensions\Query\Mysql\Radians');
    $config->addCustomNumericFunction('YEAR', '\DoctrineExtensions\Query\Mysql\Year');
    $config->addCustomNumericFunction('MONTH', '\DoctrineExtensions\Query\Mysql\Month');
    $config->addCustomNumericFunction('DATEDIFF', '\DoctrineExtensions\Query\Mysql\DateDiff');
    $config->addCustomStringFunction('DATE_FORMAT', '\DoctrineExtensions\Query\Mysql\DateFormat');
    $config->addCustomDatetimeFunction('LAST_DAY', '\DoctrineExtensions\Query\Mysql\LastDay');

    self::$entityManager = EntityManager::create($conn, $config);
  }

  public static function getEntityManager() {
    return static::$entityManager;
  }

  public static function getConfig() {
    return static::$config;
  }

  public static function getRunmode() {
    return static::$runmode;
  }
}

Bootstrap::go();
