<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../src/scrape_import_venue.php';

use \Monolog\Logger;
use \Monolog\Handler\ErrorLogHandler;
use \PF\Utilities\HttpUtil;

define('SCRAPE_PINSIDE_EXTERNAL_KEY_PREFIX', 'pinside');
define('SCRAPE_PINSIDE_LOCATION_DEFAULT_PAGE_SIZE', 100);
define('SCRAPE_PINSIDE_LOCATION_DEFAULT_LIMIT', 100);
define('SCRAPE_PINSIDE_LOCATION_COUNT_SANITY_CHECK', 3);
define('SCRAPE_PINSIDE_TRUST_GAMES', true);
define('SCRAPE_PINSIDE_RETRIES_REQUEST', 2);
define('SCRAPE_PINSIDE_SLEEP_REQUEST', 1);
define('SCRAPE_PINSIDE_DEFAULT_CONDITION', 3);
define('SCRAPE_PINSIDE_DEFAULT_PRICE', '1.00');
define('SCRAPE_PINSIDE_DATE_FORMAT', 'Y-m-d G:i:s');
define('SCRAPE_PINSIDE_DATE_TIMEZONE', 'UTC');

$ps_timezone = new DateTimeZone(SCRAPE_PINSIDE_DATE_TIMEZONE);

$opts = 'v';

$longopts = array(
  'dry-run',
  'auto-approve',
  'soft-approve',
  'tidy',
  'limit:',
  'resume-page:',
);

$options = getopt($opts, $longopts);

$verbose = isset($options['v']);
$dry_run = isset($options['dry-run']);
$auto_approve = isset($options['auto-approve']);
$soft_approve = isset($options['soft-approve']);
$tidy = isset($options['tidy']);
$limit = !empty($options['limit']) ? $options['limit'] : SCRAPE_PINSIDE_LOCATION_DEFAULT_LIMIT;
$resume_page = !empty($options['resume-page']) ? $options['resume-page'] : NULL;

$logger = Bootstrap::getLogger();
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $verbose ? Logger::DEBUG : Logger::INFO));

$time_start = microtime(true);

$iterated = 0;
$imported = 0;

$page_size = $limit < SCRAPE_PINSIDE_LOCATION_DEFAULT_PAGE_SIZE ? $limit : SCRAPE_PINSIDE_LOCATION_DEFAULT_PAGE_SIZE;
$current_page = 1;

while ($iterated < $limit) {
  if (!empty($resume_page) && $current_page < $resume_page) {
    $logger->info('Skipping page: ' . $current_page);

    $iterated += $page_size;
  }
  else {
    $logger->info('Scraping page: ' . $current_page);

    sleep(SCRAPE_PINSIDE_SLEEP_REQUEST);

    $ps_locations_json = HttpUtil::fileGetContentsRetry('https://pinside.com/api/pinsidemapping/getLocations?clustering=0&page=' . $current_page . '&limit=' . $page_size, SCRAPE_PINSIDE_RETRIES_REQUEST, SCRAPE_PINSIDE_SLEEP_REQUEST);
    //file_put_contents(__DIR__ . '/region_' . $ps_region['name'] . '.json', $ps_locations_json);
    //$ps_locations_json = file_get_contents(__DIR__ . '/region_' . $ps_region['name'] . '.json');

    if ($ps_locations_json !== FALSE) {
      $data = json_decode($ps_locations_json, TRUE);
      $ps_locations = $data['results'];
      $ps_locations_total = $data['total_results_count'];

      if (!empty($ps_locations)) {
        foreach ($ps_locations as $ps_location) {
          $logger->info('Parsing location: ' . $ps_location['location_name']);

          $venue = new \PF\Venue();

          $venue->setExternalKey(SCRAPE_PINSIDE_EXTERNAL_KEY_PREFIX . '_' . $ps_location['location_key']);
          $venue->setName(html_entity_decode($ps_location['location_name']));
          $venue->setStreet($ps_location['location_formatted_address']);
          $venue->setCity($ps_location['location_city']);
          $venue->setState($ps_location['location_state']);
          //$venue->setZipcode($ps_location['zip']);
          //$venue->setPhone($ps_location['phone']);
          $venue->setLatitude($ps_location['location_lat']);
          $venue->setLongitude($ps_location['location_lng']);
          //$venue->setUrl($ps_location['website']);
          $venue->setCreated(DateTime::createFromFormat(SCRAPE_PINSIDE_DATE_FORMAT, $ps_location['location_create_date'], $ps_timezone));
          $gamecount = $ps_location['gamecount'];

          if (!empty($gamecount)) {
            sleep(SCRAPE_PINSIDE_SLEEP_REQUEST);

            $ps_machines_json = HttpUtil::fileGetContentsRetry('https://pinside.com/api/pinsideMapping/getLocationGames?location_key=' . $ps_location['location_key'], SCRAPE_PINSIDE_RETRIES_REQUEST, SCRAPE_PINSIDE_SLEEP_REQUEST);

            if ($ps_machines_json !== FALSE) {
              $data = json_decode($ps_machines_json, TRUE);
              $ps_machines = $data['list'];

              foreach ($ps_machines as $ps_machine) {
                $logger->debug('Parsing machine: ' . $ps_machine['machine_key']);

                $machine = new \PF\Machine();

                $machine->setExternalKey(SCRAPE_PINSIDE_EXTERNAL_KEY_PREFIX . '_' . $ps_location['location_key'] . '_' . $ps_machine['machine_key']);

                $game = new \PF\Game();

                $game->setName($ps_machine['machine_name_formatted']);

                $machine->setGame($game);

                $machine->setCondition(SCRAPE_PINSIDE_DEFAULT_CONDITION);

                $machine->setPrice(SCRAPE_PINSIDE_DEFAULT_PRICE);

                $venue->addMachine($machine);
              }
            }
            else {
              $logger->warning('Could not get games for location: ' . $ps_location['location_key']);
            }
          }

          $venue->setUpdated(DateTime::createFromFormat(SCRAPE_PINSIDE_DATE_FORMAT, $ps_location['location_edit_date'], $ps_timezone));

          $scrape_import_venue_result = scrape_import_venue($venue, SCRAPE_PINSIDE_TRUST_GAMES, $auto_approve, $soft_approve, $tidy, $dry_run);

          if ($scrape_import_venue_result) {
            $imported++;
          }

          $venue = NULL;

          $iterated++;

          if ($iterated >= $limit) {
            break;
          }
        }
      } else {
        $logger->error('No locations returned for page ' . $current_page);
      }
    } else {
      $logger->error('Error getting locations at page ' . $current_page . ': ' . error_get_last()['message']);

      break;
    }
  }

  $current_page++;
}

$logger->info('New / updated venue count: ' . $imported);

$time_end = microtime(true);

$execution_time = ($time_end - $time_start);

$logger->info('Execution time: '. $execution_time .' seconds.');
