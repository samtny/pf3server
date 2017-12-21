<?php

require_once "vendor/autoload.php";

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Yaml\Parser;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Doctrine\Common\Annotations\AnnotationRegistry;

class Bootstrap {
  private static $entityManager;

  private static $config;
  private static $logger = [];
  private static $runmode;

  public static function go() {
    $parser = new Parser();

    $config = $parser->parse(file_get_contents(__DIR__ . '/config.yml'));
    self::$config = $config;

    $logger['pf3'] = new Logger('pf3');
    $logger['pf3']->pushHandler(new StreamHandler($config['pf3server_log_directory'] . '/pinfinder.log', Logger::INFO));
    $logger['pf3']->pushHandler(new StreamHandler($config['pf3server_log_directory'] . '/pinfinder_error.log', Logger::ERROR));

    $logger['pf3_scrape'] = new Logger('pf3_scrape');
    //$logger['pf3_scrape']->pushHandler(new StreamHandler($config['pf3server_log_directory'] . '/scrape.log', Logger::INFO));
    $logger['pf3_scrape']->pushHandler(new StreamHandler($config['pf3server_log_directory'] . '/scrape_error.log', Logger::ERROR));

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

    AnnotationRegistry::registerAutoloadNamespace("PF\Annotations", __DIR__ . '/src');
  }

  /**
   * @var $identifier string
   *
   * @return Logger
   */
  public static function getLogger($identifier = 'pf3') {
    return static::$logger[$identifier];
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
