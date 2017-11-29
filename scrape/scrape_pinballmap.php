<?php

require_once '../bootstrap.php';

require_once 'src/scrape_approve.php';

define('SCRAPE_PINBALLMAP_EXTERNAL_KEY_PREFIX', 'pinballmap_');

$longopts = array(
  'dry-run',
);

$options = getopt("", $longopts);

$dry_run = isset($options['dry-run']);

$region_whitelist = array(
  'nyc',
);

//$machines_json = file_get_contents('https://pinballmap.com/api/v1/machines.json');
//$regions_json = file_get_contents('https://pinballmap.com/api/v1/regions.json');

//file_put_contents(__DIR__ . '/machines.json', $machines_json);
//file_put_contents(__DIR__ . '/regions.json', $regions_json);

$pm_machines_json = file_get_contents(__DIR__ . '/machines.json');
$pm_regions_json = file_get_contents(__DIR__ . '/regions.json');

$data = json_decode($pm_machines_json, TRUE);
$pm_machines = $data['machines'];

$pm_machines_lookup = array();
foreach ($pm_machines as $machine) {
  $pm_machines_lookup[$machine['id']] = $machine;
}

$data = json_decode($pm_regions_json, TRUE);
$pm_regions = $data['regions'];

foreach ($pm_regions as $pm_region) {
  echo 'Parsing region: ' . $pm_region['name'] . "\n";

  if (in_array($pm_region['name'], $region_whitelist)) {
    //$locations_json = file_get_contents('https://pinballmap.com/api/v1/region/' . $region['name'] . '/locations.json');
    //file_put_contents(__DIR__ . '/region_' . $region['name'] . '.json', $locations_json);

    $pm_locations_json = file_get_contents(__DIR__ . '/region_' . $pm_region['name'] . '.json');

    $data = json_decode($pm_locations_json, TRUE);
    $pm_locations = $data['locations'];

    foreach ($pm_locations as $pm_location) {
      echo 'Parsing location: ' . $pm_location['name'] . "\n";

      $venue = new \PF\Venue();

      $venue->setExternalKey(SCRAPE_PINBALLMAP_EXTERNAL_KEY_PREFIX . $pm_location['id']);
      $venue->setName($pm_location['name']);
      $venue->setStreet($pm_location['street']);
      $venue->setCity($pm_location['city']);
      $venue->setState($pm_location['state']);
      $venue->setZipcode($pm_location['zip']);
      $venue->setPhone($pm_location['phone']);
      $venue->setLatitude($pm_location['lat']);
      $venue->setLongitude($pm_location['lon']);
      $venue->setUrl($pm_location['website']);
      $venue->setCreated(new DateTime($pm_location['created_at']));

      foreach ($pm_location['location_machine_xrefs'] as $pm_location_machine) {
        echo 'Parsing machine: ' . $pm_location_machine['machine_id'] . "\n";

        $machine = new \PF\Machine();

        $machine->setExternalKey(SCRAPE_PINBALLMAP_EXTERNAL_KEY_PREFIX . $pm_location_machine['id']);

        $pm_machine = $pm_machines_lookup[$pm_location_machine['machine_id']];

        $game = new \PF\Game();

        $game->setName($pm_machine['name']);

        preg_match('/ipdb.org.*id=(\d*)/', $pm_machine['ipdb_link'], $matches);

        if (count($matches) === 2) {
          echo 'Found ipdb: ' . $matches[1]. "\n";

          $game->setIpdb($matches[1]);
        }

        $machine->setGame($game);

        $venue->addMachine($machine);

        $matches = NULL;
      }

      $venue->setUpdated(new DateTime($pm_location['updated_at']));

      scrape_import($venue, $dry_run);

      $venue = NULL;
    }
  }
}
