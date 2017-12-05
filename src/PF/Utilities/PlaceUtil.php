<?php

namespace PF\Utilities;

class PlaceUtil {

  private static $text_search_base_url = "https://maps.googleapis.com/maps/api/place/textsearch/json?key=AIzaSyCdr9a-3eLJDMD-jHlGL_nxCXPC2wte2w4";

  private static $place_detail_base_url = "https://maps.googleapis.com/maps/api/place/details/json?key=AIzaSyCdr9a-3eLJDMD-jHlGL_nxCXPC2wte2w4";

  public static function nearbyPlaces($address, $venueName, $radius = 25) {
    $nearbyPlaces = NULL;

    $query = $venueName . ' near ' . $address;

    $request_url = PlaceUtil::$text_search_base_url . '&query=' . urlencode($query) . '&radius=' . $radius;

    //var_dump($request_url);exit(1);

    $ch = curl_init($request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, TRUE);

    //var_dump($data);exit(1);

    if (!empty($data['status'] && $data['status'] === 'OK')) {
      if (!empty($data['results'])) {
        $nearbyPlaces = $data['results'];
      }
    }

    return $nearbyPlaces;
  }

  public static function placeDetail($place_id) {
    $detail = NULL;

    $request_url = PlaceUtil::$place_detail_base_url . '&placeid=' . $place_id;

    //var_dump($request_url);exit(1);

    $ch = curl_init($request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, TRUE);

    if (!empty($data['status'] && $data['status'] === 'OK')) {
      if (!empty($data['result'])) {
        $detail = $data['result'];
      }
    }

    return $detail;
  }

}
