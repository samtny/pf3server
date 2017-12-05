<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../src/scrape_import_venue.php';

use \Monolog\Logger;
use \Monolog\Handler\ErrorLogHandler;

define('SCRAPE_PINBALLMAP_EXTERNAL_KEY_PREFIX', 'pinballmap');
define('SCRAPE_PINBALLMAP_REGION_COUNT_SANITY_CHECK', 10);
define('SCRAPE_PINBALLMAP_REGION_LOCATION_COUNT_SANITY_CHECK', 3);
define('SCRAPE_PINBALLMAP_CONDITION_GREAT', '/great|perfect/i');
define('SCRAPE_PINBALLMAP_CONDITION_BROKEN', '/broken|not working|out of order|turned off|^broke/i');
define('SCRAPE_PINBALLMAP_TRUST_GAMES', true);

$opts = 'v';

$longopts = array(
  'dry-run',
  'limit-region:',
  'auto-approve',
  'soft-approve',
  'tidy',
);

$options = getopt($opts, $longopts);
$verbose = isset($options['v']);
$dry_run = isset($options['dry-run']);
$limit_region = !empty($options['limit-region']) ? $options['limit-region'] : NULL;
$auto_approve = isset($options['auto-approve']);
$soft_approve = isset($options['soft-approve']);
$tidy = isset($options['tidy']);

$logger = Bootstrap::getLogger();
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $verbose ? Logger::DEBUG : Logger::INFO));

$region_whitelist = !empty($limit_region) ? array($limit_region) : array(
  //'nyc',
  //'minnesota',
  //'portland',
  //'toronto',
);

$pm_regions = array();

if (empty($limit_region)) {
  $logger->info('Retrieving region json');

  $pm_regions_json = file_get_contents('https://pinballmap.com/api/v1/regions.json');
  //file_put_contents(__DIR__ . '/regions.json', $regions_json);
  //$pm_regions_json = file_get_contents(__DIR__ . '/regions.json');

  $data = json_decode($pm_regions_json, TRUE);

  $pm_regions = $data['regions'];
} else {
  $pm_regions = array(
    array (
      'name' => $limit_region,
    ),
  );
}

if (count($pm_regions) >= SCRAPE_PINBALLMAP_REGION_COUNT_SANITY_CHECK || !empty($limit_region)) {
  $logger->info('Retrieving machines json');

  $pm_machines_json = file_get_contents('https://pinballmap.com/api/v1/machines.json');
  //file_put_contents(__DIR__ . '/machines.json', $machines_json);
  //$pm_machines_json = file_get_contents(__DIR__ . '/machines.json');

  $data = json_decode($pm_machines_json, TRUE);
  $pm_machines = $data['machines'];

  $pm_machines_lookup = array();
  foreach ($pm_machines as $machine) {
    $pm_machines_lookup[$machine['id']] = $machine;
  }

  foreach ($pm_regions as $pm_region) {
    if (in_array($pm_region['name'], $region_whitelist)) {
      $logger->info('Parsing region: ' . $pm_region['name']);

      $pm_locations_json = file_get_contents('https://pinballmap.com/api/v1/region/' . $pm_region['name'] . '/locations.json');
      //file_put_contents(__DIR__ . '/region_' . $pm_region['name'] . '.json', $pm_locations_json);
      //$pm_locations_json = file_get_contents(__DIR__ . '/region_' . $pm_region['name'] . '.json');

      if ($pm_locations_json !== FALSE) {
        $data = json_decode($pm_locations_json, TRUE);
        $pm_locations = $data['locations'];

        if (count($pm_locations) >= SCRAPE_PINBALLMAP_REGION_LOCATION_COUNT_SANITY_CHECK) {
          foreach ($pm_locations as $pm_location) {
            $logger->info('Parsing location: ' . $pm_location['name']);

            $venue = new \PF\Venue();

            $venue->setExternalKey(SCRAPE_PINBALLMAP_EXTERNAL_KEY_PREFIX . '_' . $pm_region['name'] . '_' . $pm_location['id']);
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
              $logger->debug('Parsing machine: ' . $pm_location_machine['machine_id']);

              $machine = new \PF\Machine();

              $machine->setExternalKey(SCRAPE_PINBALLMAP_EXTERNAL_KEY_PREFIX . '_' . $pm_region['name'] . '_' . $pm_location_machine['id']);

              $pm_machine = $pm_machines_lookup[$pm_location_machine['machine_id']];

              $game = new \PF\Game();

              $game->setName($pm_machine['name']);

              preg_match('/ipdb.org.*id=(\d*)/', $pm_machine['ipdb_link'], $matches);

              if (count($matches) === 2) {
                $logger->debug('Found ipdb: ' . $matches[1]);

                $game->setIpdb($matches[1]);
              }

              $machine->setGame($game);

              $machine->setCondition(pinballmap_condition_string_to_condition($pm_location_machine['condition']));

              $venue->addMachine($machine);

              $matches = NULL;
            }

            $venue->setUpdated(new DateTime($pm_location['updated_at']));

            scrape_import_venue($venue, SCRAPE_PINBALLMAP_TRUST_GAMES, $auto_approve, $soft_approve, $tidy, $dry_run);

            $venue = NULL;
          }
        }
        else {
          $logger->warning("Location count for region '" . $pm_region['name'] . "' does not pass sanity check!");
        }
      }
      else {
        $logger->warning('Error getting region \'' . $pm_region['name'] . '\' locations: ' . error_get_last()['message']);
      }
    }
  }
} else {
  $logger->error("Region count does not pass sanity check!");
}

function pinballmap_condition_string_to_condition($pm_condition) {
  $condition = 3;

  preg_match(SCRAPE_PINBALLMAP_CONDITION_GREAT, $pm_condition) === 1 && $condition = 4;
  preg_match(SCRAPE_PINBALLMAP_CONDITION_BROKEN, $pm_condition) === 1 && $condition = 0;

  return $condition;
}
