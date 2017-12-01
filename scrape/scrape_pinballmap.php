<?php

require_once __DIR__ . '/src/scrape_import.php';

define('SCRAPE_PINBALLMAP_EXTERNAL_KEY_PREFIX', 'pinballmap_');
define('SCRAPE_PINBALLMAP_REGION_COUNT_SANITY_CHECK', 10);
define('SCRAPE_PINBALLMAP_REGION_LOCATION_COUNT_SANITY_CHECK', 3);
define('SCRAPE_PINBALLMAP_CONDITION_GREAT', '/great|perfect/i');
define('SCRAPE_PINBALLMAP_CONDITION_BROKEN', '/broken|not working|out of order|turned off|^broke/i');
define('SCRAPE_PINBALLMAP_TRUST_GAMES', true);

$longopts = array(
  'dry-run',
);

$options = getopt("", $longopts);

$dry_run = isset($options['dry-run']);

$region_whitelist = array(
  'nyc',
  'minnesota',
);

$pm_machines_json = file_get_contents('https://pinballmap.com/api/v1/machines.json');
//file_put_contents(__DIR__ . '/machines.json', $machines_json);
//$pm_machines_json = file_get_contents(__DIR__ . '/machines.json');

$data = json_decode($pm_machines_json, TRUE);
$pm_machines = $data['machines'];

$pm_machines_lookup = array();
foreach ($pm_machines as $machine) {
  $pm_machines_lookup[$machine['id']] = $machine;
}

$pm_regions_json = file_get_contents('https://pinballmap.com/api/v1/regions.json');
//file_put_contents(__DIR__ . '/regions.json', $regions_json);
//$pm_regions_json = file_get_contents(__DIR__ . '/regions.json');

$data = json_decode($pm_regions_json, TRUE);
$pm_regions = $data['regions'];

if (count($pm_regions) >= SCRAPE_PINBALLMAP_REGION_COUNT_SANITY_CHECK) {
  foreach ($pm_regions as $pm_region) {
    echo 'Parsing region: ' . $pm_region['name'] . "\n";

    if (in_array($pm_region['name'], $region_whitelist)) {
      $pm_locations_json = file_get_contents('https://pinballmap.com/api/v1/region/' . $pm_region['name'] . '/locations.json');
      //file_put_contents(__DIR__ . '/region_' . $pm_region['name'] . '.json', $pm_locations_json);
      //$pm_locations_json = file_get_contents(__DIR__ . '/region_' . $pm_region['name'] . '.json');

      $data = json_decode($pm_locations_json, TRUE);
      $pm_locations = $data['locations'];

      if (count($pm_locations) >= SCRAPE_PINBALLMAP_REGION_LOCATION_COUNT_SANITY_CHECK) {
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

            $machine->setCondition(pinballmap_condition_string_to_condition($pm_location_machine['condition']));

            $venue->addMachine($machine);

            $matches = NULL;
          }

          $venue->setUpdated(new DateTime($pm_location['updated_at']));

          scrape_import($venue, SCRAPE_PINBALLMAP_TRUST_GAMES, $dry_run);

          $venue = NULL;
        }
      }
      else {
        echo "WARNING: location count for region '" . $pm_region['name'] . "' does not pass sanity check!";
      }
    }
  }
}
else {
  exit("ERROR: region count does not pass sanity check!");
}

function pinballmap_condition_string_to_condition($pm_condition) {
  $condition = 3;

  preg_match(SCRAPE_PINBALLMAP_CONDITION_GREAT, $pm_condition) === 1 && $condition = 4;
  preg_match(SCRAPE_PINBALLMAP_CONDITION_BROKEN, $pm_condition) === 1 && $condition = 0;

  return $condition;
}
