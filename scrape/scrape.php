<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/src/scrape_import_venue.php';

use \Monolog\Logger;
use \Monolog\Handler\ErrorLogHandler;
use \Doctrine\Common\Annotations\AnnotationReader;

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
  $source_class_files = glob(__DIR__ . '/../src/PF/Scrape/Source/*.{php}', GLOB_BRACE);

  $annotationReader = new AnnotationReader();
  $sourceInstance = NULL;

  foreach($source_class_files as $source_class_file) {
    $source_class_class = 'PF\Scrape\Source\\' . basename($source_class_file, '.php');

    $rc = new ReflectionClass($source_class_class);

    $classAnnotations = $annotationReader->getClassAnnotations($rc);

    foreach ($classAnnotations AS $annotation) {
      if ($annotation instanceof \PF\Annotations\ScrapeSource) {
        if ($annotation->id === $source) {
          $sourceInstance = new $source_class_class();

          break;
        }
      }
    }

    if (!empty($sourceInstance)) {
      break;
    }
  }

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
