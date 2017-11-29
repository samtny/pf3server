<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_game_lookup.php';

function scrape_machines_lookup($entityManager, $scrape_machines) {
  $machines = new \Doctrine\Common\Collections\ArrayCollection();

  foreach ($scrape_machines as $scrape_machine) {
    $scrape_game = $scrape_machine->getGame();
    $game = scrape_game_lookup($entityManager, $scrape_game);

    if (!empty($game)) {
      echo "Game found\n";

      echo "Looking up machine by external key: " . $scrape_machine->getExternalKey() . "\n";
      $machine = $entityManager->getRepository('\PF\Machine')->findOneBy(array('external_key' => $scrape_machine->getExternalKey()));

      if (empty($machine) || $machine->getGame() != $game) {
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

      $machines[] = $machine;
    }
    else {
      echo "WARNING: Game not found: " . $scrape_game->getName() . "\n";
    }
  }

  return $machines;
}
