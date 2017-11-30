<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_machine_lookup.php';

function scrape_prune_machine_exists($machine, $scrape_machines) {
  $exists = FALSE;

  foreach ($scrape_machines as $scrape_machine) {
    if ($scrape_machine->getIpdb() == $machine->getIpdb()) {
      $exists = TRUE;
    }

    if ($exists) {
      break;
    }
  }

  return $exists;
}

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 * @param bool $dry_run
 */
function scrape_prune_machines($scrape_venue, $venue, $dry_run = FALSE) {
  $entityManager = Bootstrap::getEntityManager();

  $scrape_machines = $scrape_venue->getMachines();

  $updated = FALSE;

  foreach ($venue->getMachines() as $machine) {
    if (!scrape_prune_machine_exists($machine, $scrape_machines)) {
      $machine->delete();

      $entityManager->persist($machine);

      $updated = TRUE;
    }
  }

  if ($updated && !$dry_run) {
    $entityManager->flush();
  }
}
