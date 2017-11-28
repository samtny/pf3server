<?php

require_once __DIR__ . '/../../bootstrap.php';

define('VENUE_FUZZY_LOOKUP_MAX_DISTANCE', 0.25);

function venue_route_search($entityManager, $request) {
  $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($request);

  $venues = [];

  foreach ($venuesIterator as $venue) {
    $venues[] = $venue;
  }

  return $venues;
}

function venue_lat_lon_distance($lat1, $lon1, $lat2, $lon2, $unit = 'M') {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
    return ($miles * 0.8684);
  } else {
    return $miles;
  }
}

/**
 * @param $entityManager
 * @param $scrape_venue \PF\Venue
 * @return null
 */
function venue_fuzzy_lookup($entityManager, $scrape_venue) {
  $venue = NULL;

  $request = new \PF\RequestProxy(array(
    'l' => 1,
    'n' => $scrape_venue->getLatitude() . ',' . $scrape_venue->getLongitude(),
  ));

  $venues = venue_route_search($entityManager, $request);

  if (!empty($venues)) {
    $distance = venue_lat_lon_distance($scrape_venue->getLatitude(), $scrape_venue->getLongitude(), $venues[0]->getLatitude(), $venues[0]->getLongitude(), 'M');

    echo 'Venue distance: ' . $distance . "\n";

    if ($distance <= VENUE_FUZZY_LOOKUP_MAX_DISTANCE) {
      echo 'Within range' . "\n";

      $scrape_dm = $scrape_venue->getNameDm();
      $candidate_dm = $venues[0]->getNameDm();

      if (strpos($scrape_dm, $candidate_dm) !== FALSE || strpos($candidate_dm, $scrape_dm) !== FALSE) {
        echo $scrape_dm . ' matches ' . $candidate_dm . "\n";

        $venue = $venues[0];
      } else {
        echo $scrape_dm . ' does not match ' . $candidate_dm . "\n";
      }
    }
  }

  return $venue;
}

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 *
 * @return boolean
 */
function scrape_is_fresher($scrape_venue, $venue) {
  $is_fresh = TRUE;

  ($scrape_venue->getUpdated()->getTimestamp() > $venue->getUpdated()->getTimestamp()) || $is_fresh = FALSE;
  ($scrape_venue->getMachines()->count() > 0) || $is_fresh = FALSE;

  return $is_fresh;
}

/**
 * @param $scrape_venue \PF\Venue
 */
function scrape_approve($scrape_venue) {
  $entityManager = Bootstrap::getEntityManager();

  $venue = $entityManager->getRepository('\PF\Venue')->findOneBy(array('external_key' => $scrape_venue->getExternalKey()));

  if (empty($venue)) {
    $venue = venue_fuzzy_lookup($entityManager, $scrape_venue);
  }

  if (!empty($venue)) {
    echo "Found venue: " . $venue->getId() . "\n";

    if (scrape_is_fresher($scrape_venue, $venue)) {
      echo "Scrape is fresher" . "\n";
    } else {
      echo "Scrape is not fresher" . "\n";
    }
  } else {
    echo "Creating new venue\n";
  }
}
