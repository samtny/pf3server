<?php

//$machines_json = file_get_contents('https://pinballmap.com/api/v1/machines.json');
//$regions_json = file_get_contents('https://pinballmap.com/api/v1/regions.json');

//file_put_contents(__DIR__ . '/machines.json', $machines_json);
//file_put_contents(__DIR__ . '/regions.json', $regions_json);

$machines_json = file_get_contents(__DIR__ . '/machines.json');
$regions_json = file_get_contents(__DIR__ . '/regions.json');

$data = json_decode($machines_json, TRUE);
$machines = $data['machines'];

$data = json_decode($regions_json, TRUE);
$regions = $data['regions'];

foreach ($regions as $region) {
  echo 'Parsing region: ' . $region['name'] . "\n";

  if ($region['name'] === 'nyc') {
    //$locations_json = file_get_contents('https://pinballmap.com/api/v1/region/' . $region['name'] . '/locations.json');
    //file_put_contents(__DIR__ . '/region_' . $region['name'] . '.json', $locations_json);

    $locations_json = file_get_contents(__DIR__ . '/region_' . $region['name'] . '.json');

    $data = json_decode($locations_json, TRUE);
    $locations = $data['locations'];

    foreach ($locations as $location) {
      echo 'Parsing location: ' . $location['name'] . "\n";
    }
  }

}
