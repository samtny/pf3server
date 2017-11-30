<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_game_lookup.php';

function scrape_machine_match_venue_game($scrape_machine, $venue) {
  $machine = NULL;

  $scrape_machine_game = $scrape_machine->getGame()->getId();

  foreach ($venue->getMachines() as $venue_machine) {
    $venue_machine_game = $venue_machine->getGame();

    if ($venue_machine_game->getId() == $scrape_machine_game) {
      echo "Matched machine by game id\n";
      $machine = $venue_machine;

      break;
    }
  }

  return $machine;
}

function scrape_machine_lookup($entityManager, $scrape_machine, $venue) {
  $machine = NULL;

  $scrape_game = $scrape_machine->getGame();
  $game = scrape_game_lookup($entityManager, $scrape_game);

  if (!empty($game)) {
    echo "Game found\n";

    echo "Looking up machine by external key: " . $scrape_machine->getExternalKey() . "\n";
    $machine = $entityManager->getRepository('\PF\Machine')->findOneBy(array('external_key' => $scrape_machine->getExternalKey()));

    if (empty($machine)) {
      $machine = scrape_machine_match_venue_game($scrape_machine, $venue);
    }

    if (empty($machine)) {
      $machine = new \PF\Machine();

      $machine->setExternalKey($scrape_machine->getExternalKey());
      $machine->setGame($game);
    }

    if (!empty($scrape_machine->getCondition())) {
      $machine->setCondition($scrape_machine->getCondition());
    }

    if (!empty($scrape_machine->getPrice())) {
      $machine->setPrice($scrape_machine->getPrice());
    }
  }
  else {
    echo "WARNING: Game not found: " . $scrape_game->getName() . "\n";
  }

  return $machine;
}
