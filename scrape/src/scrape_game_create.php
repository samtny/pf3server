<?php

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @param $scrape_game \PF\Game
 * @param $dry_run bool
 *
 * @return null|\PF\Game
 */
function scrape_game_create($scrape_game, $dry_run) {
  $game = $scrape_game;

  $entityManager = Bootstrap::getEntityManager();

  if (!$dry_run) {
    $entityManager->persist($game);

    $entityManager->flush();
  }

  return $game;
}
