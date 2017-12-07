<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_util.php';

use \PF\Utilities\StringUtil;

define('VENUE_MIN_UPDATED', '-2 years');
define('VENUE_VALIDATE_NO_CONFLICT_MIN_DISTANCE', 0.10);

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 *
 * @return boolean
 */
function scrape_venue_validate_fresher($scrape_venue, $venue) {
  $is_fresh = TRUE;

  if (!empty($venue->getUpdated())) {
    ($scrape_venue->getUpdated()->getTimestamp() > $venue->getUpdated()->getTimestamp()) || $is_fresh = FALSE;
  }

  return $is_fresh;
}

/**
 * @param $scrape_venue \PF\Venue
 *
 * @return bool
 */
function scrape_venue_validate($scrape_venue) {
  $is_valid = TRUE;

  $logger = Bootstrap::getLogger();

  static $min_updated;

  if (empty($min_updated)) {
    $min_updated = new DateTime(VENUE_MIN_UPDATED);

    $logger->debug("Min updated: " . $min_updated->format('c') . "\n");
  }

  $logger->debug("Scrape updated compare: " . date_diff($scrape_venue->getUpdated(), $min_updated)->format('%a') . "\n");

  ($scrape_venue->getUpdated() >= $min_updated) || $is_valid = FALSE;

  ($scrape_venue->getMachines()->count() > 0) || $is_valid = FALSE;

  return $is_valid;
}

/**
 * @param $venue \PF\Venue
 *
 * @return bool
 */
function scrape_venue_validate_complete($venue) {
  $complete = TRUE;

  if (empty($venue->getName()) || empty($venue->getStreet())) {
    $complete = FALSE;
  }

  if (empty($venue->getPhone()) && empty($venue->getUrl())) {
    $complete = FALSE;
  }

  return $complete;
}

/**
 * @param $venue \PF\Venue
 * @return bool
 */
function scrape_venue_validate_no_conflict($venue) {
  $no_conflict = TRUE;

  $entityManager = Bootstrap::getEntityManager();
  $logger = Bootstrap::getLogger();

  if (!empty($venue->getLatitude()) && !empty($venue->getLongitude())) {
    $request = new \PF\RequestProxy(array(
      'l' => 1,
      'n' => $venue->getLatitude() . ',' . $venue->getLongitude(),
    ));

    $venues = venue_request($entityManager, $request);

    if (!empty($venues)) {
      foreach ($venues as $candidate_venue) {
        $distance = _venue_lat_lon_distance($venue->getLatitude(), $venue->getLongitude(), $candidate_venue->getLatitude(), $candidate_venue->getLongitude(), 'M');

        $logger->debug('Venue distance: ' . $distance . "\n");

        if ($distance <= VENUE_VALIDATE_NO_CONFLICT_MIN_DISTANCE) {

          if (empty($venue->getId()) || $venue->getId() != $candidate_venue->getId()) {
            $logger->debug('Too close' . "\n");

            $no_conflict = FALSE;

            break;
          } else if (!empty($venue->getExternalKey()) && empty($candidate_venue->getExternalKey())) {
            $logger->debug('Too close' . "\n");

            $no_conflict = FALSE;

            break;
          }

        }
      }
    }
  }

  return $no_conflict;
}
