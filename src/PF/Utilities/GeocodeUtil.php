<?php

namespace PF\Utilities;

use PF\Geocode;

class GeocodeUtil {

  private static $base_url = "https://maps.googleapis.com/maps/api/geocode/xml?sensor=false&key=AIzaSyCdr9a-3eLJDMD-jHlGL_nxCXPC2wte2w4";

  private static $tooSmall = array(
    "street_address",
    "intersection",
    "premise",
    "subpremise",
    "natural_feature",
    "park",
    "point_of_interest",
    "post_box",
    "street_number",
    "floor",
    "room"
  );

  public static function geocodeString($string) {
    $geocode = new Geocode();

    $geocode->setString($string);

    $request_url = GeocodeUtil::$base_url . "&address=" . urlencode($string);

    $ch = curl_init($request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $response = curl_exec($ch);
    curl_close($ch);

    $xml = simplexml_load_string($response);

    $status = $xml->status;

    if (strcmp($status, "OK") == 0) {
      $geocode->setCoordinateLatitude((float)$xml->result[0]->geometry->location->lat);
      $geocode->setCoordinateLongitude((float)$xml->result[0]->geometry->location->lng);

      $type = $xml->result[0]->type;
      if (!$type) {
        $type = $xml->result[0]->type[0];
      }

      if (!in_array($type, GeocodeUtil::$tooSmall)) {
        if ($xml->result[0]->geometry->bounds) {
          $geocode->setSouthwestLatitude((float)$xml->result[0]->geometry->bounds->southwest->lat);
          $geocode->setSouthwestLongitude((float)$xml->result[0]->geometry->bounds->southwest->lng);
          $geocode->setNortheastLatitude((float)$xml->result[0]->geometry->bounds->northeast->lat);
          $geocode->setNortheastLongitude((float)$xml->result[0]->geometry->bounds->northeast->lng);
        } else {
          $geocode->setSouthwestLatitude((float)$xml->result[0]->geometry->viewport->southwest->lat);
          $geocode->setSouthwestLongitude((float)$xml->result[0]->geometry->viewport->southwest->lng);
          $geocode->setNortheastLatitude((float)$xml->result[0]->geometry->viewport->northeast->lat);
          $geocode->setNortheastLongitude((float)$xml->result[0]->geometry->viewport->northeast->lng);
        }
      }
    }

    return $geocode;
  }

}
