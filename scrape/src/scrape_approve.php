<?php

require_once __DIR__ . '/../../bootstrap.php';

define('VENUE_FUZZY_LOOKUP_MAX_DISTANCE', 0.25);
define('VENUE_MIN_UPDATED', '-2 years');

function venue_request($entityManager, $request) {
  $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($request);

  $venues = [];

  foreach ($venuesIterator as $venue) {
    $venues[] = $venue;
  }

  return $venues;
}

function game_request($entityManager, $request) {
  $gamesIterator = $entityManager->getRepository('\PF\Game')->getGames($request);

  $games = [];

  foreach ($gamesIterator as $game) {
    $games[] = $game;
  }

  return $games;
}

function venue_lat_lon_distance($lat1, $lon1, $lat2, $lon2, $unit = 'M') {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
    return ($miles * 0.8684);
  } else {
    return $miles;
  }
}

/**
 * @param $entityManager
 * @param $scrape_venue \PF\Venue
 * @return null
 */
function venue_fuzzy_lookup($entityManager, $scrape_venue) {
  $venue = NULL;

  $request = new \PF\RequestProxy(array(
    'l' => 1,
    'n' => $scrape_venue->getLatitude() . ',' . $scrape_venue->getLongitude(),
  ));

  $venues = venue_request($entityManager, $request);

  if (!empty($venues)) {
    $distance = venue_lat_lon_distance($scrape_venue->getLatitude(), $scrape_venue->getLongitude(), $venues[0]->getLatitude(), $venues[0]->getLongitude(), 'M');

    echo 'Venue distance: ' . $distance . "\n";

    if ($distance <= VENUE_FUZZY_LOOKUP_MAX_DISTANCE) {
      echo 'Within range' . "\n";

      $scrape_dm = $scrape_venue->getNameDm();
      $candidate_dm = $venues[0]->getNameDm();

      if (strpos($scrape_dm, $candidate_dm) !== FALSE || strpos($candidate_dm, $scrape_dm) !== FALSE) {
        echo $scrape_dm . ' matches ' . $candidate_dm . "\n";

        $venue = $venues[0];
      } else {
        echo $scrape_dm . ' does not match ' . $candidate_dm . "\n";
      }
    }
  }

  return $venue;
}

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 *
 * @return boolean
 */
function scrape_is_fresher($scrape_venue, $venue) {
  $is_fresh = TRUE;

  ($scrape_venue->getUpdated()->getTimestamp() > $venue->getUpdated()->getTimestamp()) || $is_fresh = FALSE;
  ($scrape_venue->getMachines()->count() > 0) || $is_fresh = FALSE;

  return $is_fresh;
}

/**
 * @param $scrape_venue \PF\Venue
 * @return bool
 */
function scrape_validate($scrape_venue) {
  $is_valid = TRUE;

  static $min_updated;

  if (empty($min_updated)) {
    $min_updated = new DateTime(VENUE_MIN_UPDATED);

    echo "Min updated: " . $min_updated->format('c') . "\n";
  }

  echo "Scrape updated compare: " . date_diff($scrape_venue->getUpdated(), $min_updated)->format('%a') . "\n";

  ($scrape_venue->getUpdated() >= $min_updated) || $is_valid = FALSE;

  return $is_valid;
}

function game_shortest_name_sort($a, $b) {
  return strlen($a) < strlen($b) ? -1 : 1;
}

/**
 * @param $entityManager \Doctrine\ORM\EntityManager
 * @param $scrape_game \PF\Game
 * @return null
 */
function game_fuzzy_lookup($entityManager, $scrape_game) {
  $game = NULL;

  $scrape_name = $scrape_game->getName();

  $request = new \PF\RequestProxy(array(
    'q' => $scrape_game->getName(),
  ));

  $candidate_games = game_request($entityManager, $request);

  if (!empty($candidate_games)) {
    usort($candidate_games, 'game_shortest_name_sort');

    $scrape_dm = $scrape_game->getNameDm();

    foreach ($candidate_games as $candidate_game) {
      $candidate_dm = $candidate_game->getNameDm();
      $candidate_name = $candidate_game->getName();

      if (!empty($candidate_dm) && !empty($scrape_dm)) {
        if (strpos($scrape_dm, $candidate_dm) === 0 || strpos($candidate_dm, $scrape_dm) === 0) {
          echo 'Game dm: ' . $scrape_dm . ' matches candidate dm: ' . $candidate_dm . "\n";

          $game = $candidate_game;

          break;
        }
      }

      if (!empty($candidate_name) && !empty($scrape_name)) {
        if (strpos($scrape_name, $candidate_name) === 0 || strpos($candidate_name, $scrape_name) === 0) {
          echo 'Game name: ' . $scrape_game->getName() . ' matches candidate name: ' . $candidate_game->getName() . "\n";

          $game = $candidate_game;

          break;
        }
      }
    }

    if (empty($game)) {
      echo "WARNING: game matches no candidates: " . $scrape_game->getName() . "\n";
    }
  } else {
    echo "WARNING: no candidates for game: " . $scrape_game->getName() . "\n";
  }

  return $game;
}

/**
 * @param $entityManager \Doctrine\ORM\EntityManager
 * @param $scrape_game \PF\Game
 * @return null
 */
function game_fuzzy_lookup_cached($entityManager, $scrape_game) {
  $game = NULL;

  static $game_fuzzy_lookup_cache = array();

  $scrape_name = $scrape_game->getName();
  $scrape_ipdb = $scrape_game->getIpdb();
  $scrape_cache_key = (!empty($scrape_name) ? $scrape_name : '') . '_' . (!empty($scrape_ipdb) ? $scrape_ipdb : '');
  $scrape_cache_key_md5 = md5($scrape_cache_key);

  if (key_exists($scrape_cache_key_md5, $game_fuzzy_lookup_cache)) {
    echo "Game cache lookup hit\n";
    $id = $game_fuzzy_lookup_cache[$scrape_cache_key_md5];

    if (!empty($id)) {
      $game = $entityManager->getRepository('\PF\Game')->find($id);
    }
  } else {
    echo "Game cache lookup miss\n";
    $game = game_fuzzy_lookup($entityManager, $scrape_game);

    $game_fuzzy_lookup_cache[$scrape_cache_key_md5] = !empty($game) ? $game->getId() : NULL;
  }

  return $game;
}

/**
 * @param $scrape_venue \PF\Venue
 */
function scrape_lookup_games($entityManager, $scrape_venue) {
  $machines = new \Doctrine\Common\Collections\ArrayCollection();

  foreach ($scrape_venue->getMachines() as &$scrape_machine) {
    $scrape_game = $scrape_machine->getGame();
    $game = NULL;

    if (!empty($scrape_game->getIpdb())) {
      echo "Looking up game by ipdb: " . $scrape_game->getIpdb() . "\n";

      $game = $entityManager->getRepository('\PF\Game')->findOneBy(array('ipdb' => $scrape_game->getIpdb()));

      if (!empty($game)) {
        echo "Found game by ipdb: " . $game->getName() . "\n";
      }
      else {
        echo "WARNING: Game not found by ipdb: " . $scrape_game->getIpdb() . "\n";
      }
    }

    if (empty($game)) {
      echo "Looking up game by fuzzy: " . $scrape_game->getName() . "\n";

      $game = game_fuzzy_lookup_cached($entityManager, $scrape_game);

      if (!empty($game)) {
        echo "Found game by fuzzy: " . $game->getName() . "\n";
      }
      else {
        echo "WARNING: Game not found by fuzzy: " . $scrape_game->getName() . "\n";
      }
    }

    if (!empty($game)) {
      echo "Game found\n";

      $scrape_machine->setGame($game);
      $machines[] = $scrape_machine;
    }
    else {
      echo "WARNING: Game not found: " . $scrape_game->getName() . "\n";
    }
  }

  $scrape_venue->setMachines($machines, TRUE);
}

/**
 * @param $scrape_venue \PF\Venue
 */
function scrape_import($scrape_venue, $dry_run = FALSE) {
  $entityManager = Bootstrap::getEntityManager();

  if (scrape_validate($scrape_venue)) {
    echo "Scrape passes validation" . "\n";

    scrape_lookup_games($entityManager, $scrape_venue);

    echo "Looking up venue by external key: " . $scrape_venue->getExternalKey() . "\n";
    $venue = $entityManager->getRepository('\PF\Venue')->findOneBy(array('external_key' => $scrape_venue->getExternalKey()));

    if (empty($venue)) {
      echo "Looking up venue by fuzzy" . "\n";

      $venue = venue_fuzzy_lookup($entityManager, $scrape_venue);
    }

    if (!empty($venue)) {
      echo "Found venue: " . $venue->getId() . "\n";

      if (scrape_is_fresher($scrape_venue, $venue)) {
        echo "Scrape is fresher" . "\n";

        echo "Updating venue: " . $venue->getId() . "\n";

        if (!$dry_run) {
          // FIXME:
        }
      } else {
        echo "Scrape is not fresher" . "\n";

        echo "Declining to update venue: " . $venue->getId() . "\n";
      }
    } else {
      echo "Creating new venue\n";

      if (!$dry_run) {
        $entityManager->persist($scrape_venue);

        $entityManager->flush();
      }
    }
  } else {
    echo "Scrape does not pass validation" . "\n";
  }
}
