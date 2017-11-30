<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_game_lookup.php';

/**
 * @param $scrape_venue \PF\Venue
 * @param $dry_run bool
 */
function scrape_import_games($scrape_venue, $dry_run) {
  $entityManager = Bootstrap::getEntityManager();

  foreach ($scrape_venue->getMachines() as $scrape_machine) {
    /**
     * @var $scrape_machine \PF\Machine
     */
    $scrape_game = $scrape_machine->getGame();

    $game = scrape_game_lookup($scrape_game);

    if (empty($game)) {
      $game = new \PF\Game();
      $game->setIpdb($scrape_game->getIpdb());
      $game->setName($scrape_game->getName());

      if (!$dry_run) {
        $entityManager->persist($game);

        $entityManager->flush();
      }
    }
  }
}
