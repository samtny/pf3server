<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_request.php';

define('VENUE_FUZZY_LOOKUP_MAX_DISTANCE', 0.25);

function _venue_lat_lon_distance($lat1, $lon1, $lat2, $lon2, $unit = 'M') {

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
function scrape_venue_fuzzy_lookup($entityManager, $scrape_venue) {
  $venue = NULL;

  $request = new \PF\RequestProxy(array(
    'l' => 1,
    'n' => $scrape_venue->getLatitude() . ',' . $scrape_venue->getLongitude(),
  ));

  $venues = venue_request($entityManager, $request);

  if (!empty($venues)) {
    $distance = _venue_lat_lon_distance($scrape_venue->getLatitude(), $scrape_venue->getLongitude(), $venues[0]->getLatitude(), $venues[0]->getLongitude(), 'M');

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
