<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_game_lookup.php';

/**
 * @param $scrape_machine \PF\Machine
 * @param $venue \PF\Venue
 *
 * @return null|\PF\Machine
 */
function scrape_machine_match_venue_game($scrape_machine, $venue) {
  $machine = NULL;

  $logger = Bootstrap::getLogger('pf3_scrape');

  $scrape_machine_game_id = $scrape_machine->getGame()->getId();
  $scrape_machine_game_name = $scrape_machine->getGame()->getName();

  foreach ($venue->getMachines() as $venue_machine) {
    /** @var $venue_machine_game \PF\Game **/
    $venue_machine_game = $venue_machine->getGame();

    if (!empty($scrape_machine_game_id) && ($venue_machine_game->getId() == $scrape_machine_game_id)) {
      $logger->debug("Matched machine by game id\n");
      $machine = $venue_machine;

      break;
    }
    else if (!empty($venue_machine_game) && $venue_machine_game->getName() == $scrape_machine_game_name) {
      $logger->debug("Matched machine by game name\n");
      $machine = $venue_machine;

      break;
    }
  }

  return $machine;
}

/**
 * @param $scrape_machine \PF\Machine
 * @param $venue \PF\Venue
 *
 * @return null|\PF\Machine
 */
function scrape_machine_lookup($scrape_machine, $venue) {
  $machine = NULL;

  $entityManager = Bootstrap::getEntityManager();
  $logger = Bootstrap::getLogger('pf3_scrape');

  $logger->debug("Looking up machine by external key: " . $scrape_machine->getExternalKey() . "\n");
  $machine = $entityManager->getRepository('\PF\Machine')->findOneBy(array('external_key' => $scrape_machine->getExternalKey()));

  if (empty($machine)) {
    $machine = scrape_machine_match_venue_game($scrape_machine, $venue);
  }

  return $machine;
}
