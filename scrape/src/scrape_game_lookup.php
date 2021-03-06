<?php

require_once __DIR__ . '/../../bootstrap.php';

define('GAME_FUZZY_LOOKUP_STRING_MATCH_THRESHOLD', 80);

function _game_shortest_name_sort($a, $b) {
  return strlen($a) < strlen($b) ? -1 : 1;
}

/**
 * @param $scrape_game \PF\Game
 * @return null|\PF\Game
 */
function scrape_game_name_lookup_table($scrape_game) {
  $game = NULL;

  $entityManager = Bootstrap::getEntityManager();

  if (!empty($scrape_game->getName())) {
    /**
     * @var $game_lookup \PF\GameLookup
     */
    $game_lookup = $entityManager->getRepository('\PF\GameLookup')->findOneBy(array('lookup_string' => $scrape_game->getName()));

    if (!empty($game_lookup)) {
      $game = $game_lookup->getGame();
    }
  }

  return $game;
}

/**
 * @param $entityManager \Doctrine\ORM\EntityManager
 * @param $scrape_game \PF\Game
 * @return null
 */
function scrape_game_fuzzy_lookup($entityManager, $scrape_game) {
  $game = NULL;

  $logger = Bootstrap::getLogger('pf3_scrape');

  $scrape_name = $scrape_game->getName();

  $request = new \PF\RequestProxy(array(
    'q' => $scrape_game->getName(),
    'l' => 20,
  ));

  $candidate_games = game_request($entityManager, $request);

  if (!empty($candidate_games)) {
    usort($candidate_games, '_game_shortest_name_sort');

    $scrape_dm = $scrape_game->getNameDm();

    foreach ($candidate_games as $candidate_game) {
      $candidate_dm = $candidate_game->getNameDm();
      $candidate_name = $candidate_game->getName();

      if (!empty($candidate_dm) && !empty($scrape_dm)) {

        similar_text($scrape_dm, $candidate_dm, $percent);

        if ($percent > GAME_FUZZY_LOOKUP_STRING_MATCH_THRESHOLD) {
          $logger->debug('Game dm: ' . $scrape_dm . ' matches candidate dm: ' . $candidate_dm . "\n");

          $game = $candidate_game;

          break;
        }
      }

      if (!empty($candidate_name) && !empty($scrape_name)) {
        similar_text($scrape_name, $candidate_name, $percent);

        if ($percent > GAME_FUZZY_LOOKUP_STRING_MATCH_THRESHOLD) {
          $logger->debug('Game name: ' . $scrape_game->getName() . ' matches candidate name: ' . $candidate_game->getName() . "\n");

          $game = $candidate_game;

          break;
        }
      }
    }

    if (empty($game)) {
      $logger->info("Game matches no candidates: " . $scrape_game->getName() . "\n");
    }
  } else {
    $logger->info("No candidates for game: " . $scrape_game->getName() . "\n");
  }

  return $game;
}

/**
 * @param $entityManager \Doctrine\ORM\EntityManager
 * @param $scrape_game \PF\Game
 * @return null
 */
function scrape_game_fuzzy_lookup_cached($entityManager, $scrape_game) {
  $game = NULL;

  $logger = Bootstrap::getLogger('pf3_scrape');

  static $game_fuzzy_lookup_cache = array();

  $scrape_name = $scrape_game->getName();
  $scrape_ipdb = $scrape_game->getIpdb();
  $scrape_cache_key = (!empty($scrape_name) ? $scrape_name : '') . '_' . (!empty($scrape_ipdb) ? $scrape_ipdb : '');
  $scrape_cache_key_md5 = md5($scrape_cache_key);

  if (key_exists($scrape_cache_key_md5, $game_fuzzy_lookup_cache)) {
    $logger->debug("Game cache lookup hit\n");
    $id = $game_fuzzy_lookup_cache[$scrape_cache_key_md5];

    if (!empty($id)) {
      $game = $entityManager->getRepository('\PF\Game')->find($id);
    }
  } else {
    $logger->debug("Game cache lookup miss\n");
    $game = scrape_game_fuzzy_lookup($entityManager, $scrape_game);

    $game_fuzzy_lookup_cache[$scrape_cache_key_md5] = !empty($game) ? $game->getId() : NULL;
  }

  return $game;
}

/**
 * @param $scrape_game \PF\Game
 * @return null|object|\PF\Game
 */
function scrape_game_lookup($scrape_game) {
  $game = NULL;

  $entityManager = Bootstrap::getEntityManager();
  $logger = Bootstrap::getLogger('pf3_scrape');

  if (!empty($scrape_game->getIpdb())) {
    $logger->debug('Looking up game by ipdb: ' . $scrape_game->getIpdb());

    $game = $entityManager->getRepository('\PF\Game')->findOneBy(array('ipdb' => $scrape_game->getIpdb()));

    if (!empty($game)) {
      $logger->debug('Found game by ipdb: ' . $game->getName());
    }
    else {
      $logger->debug('Game not found by ipdb: ' . $scrape_game->getIpdb());
    }
  }

  if (empty($game)) {
    $logger->debug('Looking up game by fuzzy: ' . $scrape_game->getName());

    $game = scrape_game_fuzzy_lookup($entityManager, $scrape_game);

    if (!empty($game)) {
      $logger->debug('Found game by fuzzy: ' . $game->getName());
    }
    else {
      $logger->debug('Game not found by fuzzy: ' . $scrape_game->getName());
    }
  }

  if (empty($game)) {
    $logger->debug('Looking up game by lookup table: ' . $scrape_game->getName());

    $game = scrape_game_name_lookup_table($scrape_game);

    if (!empty($game)) {
      $logger->debug('Found game by lookup table: ' . $game->getName());
    }
    else {
      $logger->debug('Game not found by lookup table: ' . $scrape_game->getName());
    }
  }

  if (empty($game)) {
    $logger->warning('Game not found: ' . $scrape_game->getName());
  }

  return $game;
}
