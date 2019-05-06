<?php

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @param $entityManager \Doctrine\ORM\EntityManager
 * @param $request \PF\RequestProxy
 * @return \PF\Venue[]
 */
function venue_request($entityManager, $request) {
  $params = $request->getQueryParams();

  $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($params);

  $venues = [];

  foreach ($venuesIterator as $venue) {
    $venues[] = $venue;
  }

  return $venues;
}

/**
 * @param $entityManager \Doctrine\ORM\EntityManager
 * @param $request \PF\RequestProxy
 * @return \PF\Game[]
 */
function game_request($entityManager, $request) {
  $params = $request->getQueryParams();

  $gamesIterator = $entityManager->getRepository('\PF\Game')->getGames($params);

  $games = [];

  foreach ($gamesIterator as $game) {
    $games[] = $game;
  }

  return $games;
}
