<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Yaml\Parser;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once "vendor/autoload.php";

class Bootstrap {
  private static $entityManager;

  private static $config;
  private static $logger;
  private static $runmode;

  public static function go() {
    $parser = new Parser();

    $config = $parser->parse(file_get_contents(__DIR__ . '/config.yml'));
    self::$config = $config;

    $logger = new Logger('pf3');

    $logger->pushHandler(new StreamHandler($config['pf3server_log_directory'] . '/pinfinder.log', Logger::DEBUG));
    $logger->pushHandler(new StreamHandler($config['pf3server_log_directory'] . '/pinfinder_error.log', Logger::ERROR));

    self::$logger = $logger;

    self::$runmode = $config['pf3server_runmode'];

    $credentials = $parser->parse(file_get_contents(__DIR__ . '/credentials.yml'));

    $conn = array(
      'driver' => 'pdo_mysql',
      'dbname' => $credentials['pf3server_db_name'],
      'user' => $credentials['pf3server_db_user'],
      'password' => $credentials['pf3server_db_password'],
      'host' => $credentials['pf3server_db_host'],
      'driverOptions' => array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
      )
    );

    $proxy_dir = __DIR__ . '/cache';

    $cache_impl = (self::$runmode === 'production' && extension_loaded('apc')) ? new \Doctrine\Common\Cache\ApcuCache() : null;

    $config = Setup::createYAMLMetadataConfiguration(array(__DIR__ . '/src/PF/Doctrine/yml'), self::$runmode !== 'production', $proxy_dir, $cache_impl);
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

  /**
   * @return Logger
   */
  public static function getLogger() {
    return static::$logger;
  }

  /**
   * @return EntityManager
   */
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
