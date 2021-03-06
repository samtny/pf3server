<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_machine_lookup.php';

define('SCRAPE_IMPORT_MACHINE_DEFAULT_PRICE', '1.00');

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 * @param bool $dry_run
 */
function scrape_import_machines($scrape_venue, $venue, $dry_run = FALSE) {
  $entityManager = Bootstrap::getEntityManager();

  $updated = FALSE;

  foreach ($scrape_venue->getMachines() as $scrape_machine) {
    $machine = scrape_machine_lookup($scrape_machine, $venue);

    if (empty($machine)) {
      $scrape_game = $scrape_machine->getGame();

      $game = scrape_game_lookup($scrape_game);

      if (!empty($game)) {
        $machine = new \PF\Machine();

        $machine->setExternalKey($scrape_machine->getExternalKey());
        $machine->setGame($game);
      }
    }

    if (!empty($machine)) {
      if ($scrape_machine->getCondition() !== NULL) {
        $machine->setCondition($scrape_machine->getCondition());
      }

      if (!empty($scrape_machine->getPrice())) {
        $machine->setPrice($scrape_machine->getPrice());
      } else {
        $machine->setPrice(SCRAPE_IMPORT_MACHINE_DEFAULT_PRICE);
      }

      $machine->activate();

      $venue->addMachine($machine, TRUE);

      $updated = TRUE;
    }
  }

  if ($updated && !$dry_run) {
    $entityManager->persist($venue);

    $entityManager->flush();
  }
}
