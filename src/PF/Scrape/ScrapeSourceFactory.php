<?php

namespace PF\Scrape;

use Doctrine\Common\Annotations\AnnotationReader;
use PF\Annotations\ScrapeSourceAnnotation;
use ReflectionClass;

class ScrapeSourceFactory {
  public static function createScrapeSourceInstance($sourceId) {
    $sourceInstance = NULL;

    $source_class_files = glob(__DIR__ . '/Source/*.{php}', GLOB_BRACE);

    $annotationReader = new AnnotationReader();

    foreach($source_class_files as $source_class_file) {
      $source_class_class = 'PF\Scrape\Source\\' . basename($source_class_file, '.php');

      $rc = new ReflectionClass($source_class_class);

      $classAnnotations = $annotationReader->getClassAnnotations($rc);

      foreach ($classAnnotations AS $annotation) {
        if ($annotation instanceof ScrapeSourceAnnotation) {
          if ($annotation->id === $sourceId) {
            $sourceInstance = new $source_class_class();

            break;
          }
        }
      }

      if (!empty($sourceInstance)) {
        break;
      }
    }

    return $sourceInstance;
  }
}
