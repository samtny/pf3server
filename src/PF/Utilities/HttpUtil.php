<?php

namespace PF\Utilities;

include_once __DIR__ . '/../../../bootstrap.php';

use Bootstrap;

class HttpUtil {
  public static function fileGetContentsRetry($url, $attempts = 1, $sleep = 0) {
    $contents = FALSE;

    $logger = Bootstrap::getLogger();

    $current_attempt = 1;

    while ($contents === FALSE && $current_attempt <= $attempts) {
      if ($current_attempt > 1) {
        $logger->warning('Attempt ' . $current_attempt . ' to retrieve url.', array('url' => $url));

        sleep($sleep);
      }

      $contents = file_get_contents($url);

      $current_attempt++;
    }

    return $contents;
  }
}
