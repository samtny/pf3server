<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/src/scrape_import_venue.php';

use \Monolog\Logger;
use \Monolog\Handler\ErrorLogHandler;
use \PF\Scrape\ScrapeSourceFactory;

$opts = 'v';

$longopts = array(
  'source:',
  'dry-run',
  'limit-region:',
  'auto-approve',
  'soft-approve',
  'tidy',
  'resume-region:',
);

$options = getopt($opts, $longopts);

$verbose = isset($options['v']);

$logger = Bootstrap::getLogger('pf3_scrape');
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $verbose ? Logger::DEBUG : Logger::INFO));

$source = !empty($options['source']) ? $options['source'] : NULL;

if (!empty($source)) {
  $sourceInstance = ScrapeSourceFactory::createScrapeSourceInstance($source);

  if (!empty($sourceInstance)) {
    $dry_run = isset($options['dry-run']);
    $limit_region = !empty($options['limit-region']) ? $options['limit-region'] : NULL;
    $auto_approve = isset($options['auto-approve']);
    $soft_approve = isset($options['soft-approve']);
    $tidy = isset($options['tidy']);
    $resume_region = !empty($options['resume-region']) ? $options['resume-region'] : NULL;

    $time_start = microtime(true);

    // TODO: call source intance methods...

  } else {
    $logger->warning('Invalid source specified - aborting!');
  }
} else {
  $logger->warning('Empty source specified - aborting!');
}
