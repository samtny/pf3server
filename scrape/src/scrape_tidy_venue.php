<?php

define('SCRAPE_TIDY_VENUE_NEARBY_THRESHOLD_METERS', 25);

use PF\Utilities\PlaceUtil;
use PF\Utilities\StringUtil;

/**
 * @param $venue \PF\Venue
 * @param $placeDetail
 * @return mixed
 */
function scrape_tidy_venue_merge_place($venue, $placeDetail) {

  if (!empty($placeDetail['geometry']['location']['lat']) && !empty($placeDetail['geometry']['location']['lng'])) {
    $venue->setLatitude($placeDetail['geometry']['location']['lat']);
    $venue->setLongitude($placeDetail['geometry']['location']['lng']);
  }

  if (!empty($placeDetail['address_components'])) {
    $streetNumber = NULL;
    $route = NULL;
    $zipcode = NULL;
    $city = NULL;
    $state = NULL;

    foreach ($placeDetail['address_components'] as $address_component) {
      if (in_array('street_number', $address_component['types'])) {
        $streetNumber = $address_component['short_name'];
      } else if (in_array('route', $address_component['types'])) {
        $route = $address_component['short_name'];
      } else if (in_array('postal_code', $address_component['types'])) {
        $zipcode = $address_component['short_name'];
      } else if (in_array('locality', $address_component['types'])) {
        $city = $address_component['short_name'];
      } else if (in_array('administrative_area_level_1', $address_component['types'])) {
        $state = $address_component['short_name'];
      }
    }

    if (!empty($streetNumber) && !empty($route)) {
      $venue->setStreet($streetNumber . ' ' . $route);
    }

    if (!empty($city)) {
      $venue->setCity($city);
    }

    if (!empty($state)) {
      $venue->setState($state);
    }

    if (!empty($zipcode)) {
      $venue->setZipcode($zipcode);
    }
  }

  if (empty($venue->getPhone()) && !empty($placeDetail['formatted_phone_number'])) {
    $venue->setPhone($placeDetail['formatted_phone_number']);
  }

  if (empty($venue->getUrl()) && !empty($placeDetail['website'])) {
    $venue->setUrl($placeDetail['website']);
  }

  return $venue;
}

/**
 * @param $venue \PF\Venue
 * @return mixed
 */
function scrape_tidy_venue($venue) {

  $venueShortAddress = $venue->getStreet() . ', ' . $venue->getCity();

  //$places = PlaceUtil::nearbyPlaces($venueShortAddress, $venue->getName(), SCRAPE_TIDY_VENUE_NEARBY_THRESHOLD_METERS);

  if (!empty($places)) {
    foreach ($places as $place) {
      if (StringUtil::namesAreSimilar($venue->getName(), $place['name'])) {
        //$placeDetail = PlaceUtil::placeDetail($place['place_id']);

        if (!empty($placeDetail)) {
          $venue = scrape_tidy_venue_merge_place($venue, $placeDetail);
        }

        break;
      }
    }
  }

  return $venue;
}
