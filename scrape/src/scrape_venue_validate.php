<?php

require_once __DIR__ . '/../../bootstrap.php';

define('VENUE_MIN_UPDATED', '-2 years');

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

  static $min_updated;

  if (empty($min_updated)) {
    $min_updated = new DateTime(VENUE_MIN_UPDATED);

    echo "Min updated: " . $min_updated->format('c') . "\n";
  }

  echo "Scrape updated compare: " . date_diff($scrape_venue->getUpdated(), $min_updated)->format('%a') . "\n";

  ($scrape_venue->getUpdated() >= $min_updated) || $is_valid = FALSE;

  ($scrape_venue->getMachines()->count() > 0) || $is_valid = FALSE;

  return $is_valid;
}
