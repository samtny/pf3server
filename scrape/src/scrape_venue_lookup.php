<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_request.php';
require_once __DIR__ . '/scrape_util.php';

use \PF\Utilities\StringUtil;

define('VENUE_FUZZY_LOOKUP_MAX_DISTANCE', 0.25);
define('VENUE_FUZZY_LOOKUP_NAME_MATCH_THRESHOLD', 80);

/**
 * @param $entityManager
 * @param $scrape_venue \PF\Venue
 * @return \PF\Venue|null
 */
function scrape_venue_fuzzy_lookup($entityManager, $scrape_venue) {
  $venue = NULL;

  $logger = Bootstrap::getLogger();

  $request = new \PF\RequestProxy(array(
    'l' => 10,
    'n' => $scrape_venue->getLatitude() . ',' . $scrape_venue->getLongitude(),
  ));

  $venues = venue_request($entityManager, $request);

  if (!empty($venues)) {
    foreach ($venues as $candidate_venue) {
      $distance = _venue_lat_lon_distance($scrape_venue->getLatitude(), $scrape_venue->getLongitude(), $candidate_venue->getLatitude(), $candidate_venue->getLongitude(), 'M');

      $logger->debug('Venue distance: ' . $distance . "\n");

      if ($distance <= VENUE_FUZZY_LOOKUP_MAX_DISTANCE) {
        $logger->debug('Within range' . "\n");

        if (StringUtil::namesAreSimilar($scrape_venue->getName(), $candidate_venue->getName(), VENUE_FUZZY_LOOKUP_NAME_MATCH_THRESHOLD)) {
          $logger->debug($scrape_venue->getName() . ' matches ' . $candidate_venue->getName() . "\n");

          $venue = $candidate_venue;

          break;
        }
      }
    }
  }

  return $venue;
}

/**
 * @param $scrape_venue \PF\Venue
 * @return \PF\Venue|null
 */
function scrape_venue_lookup($scrape_venue) {
  $venue = NULL;

  $entityManager = Bootstrap::getEntityManager();
  $logger = Bootstrap::getLogger();

  $logger->debug("Looking up venue by external key: " . $scrape_venue->getExternalKey() . "\n");
  $venue = $entityManager->getRepository('\PF\Venue')->findOneBy(array('external_key' => $scrape_venue->getExternalKey()));

  if (empty($venue)) {
    $logger->debug("Looking up venue by fuzzy" . "\n");

    $venue = scrape_venue_fuzzy_lookup($entityManager, $scrape_venue);
  }

  return $venue;
}
