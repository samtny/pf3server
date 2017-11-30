<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_machine_lookup.php';

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 * @param bool $dry_run
 */
function scrape_import_machines($scrape_venue, $venue, $dry_run = FALSE) {
  $entityManager = Bootstrap::getEntityManager();

  $updated = FALSE;

  foreach ($scrape_venue->getMachines() as $scrape_machine) {
    $machine = scrape_machine_lookup($entityManager, $scrape_machine, $venue);

    if (!empty($machine)) {
      $venue->addMachine($machine, TRUE);

      $updated = TRUE;
    }
  }

  if ($updated && !$dry_run) {
    $entityManager->persist($venue);

    $entityManager->flush();
  }
}
