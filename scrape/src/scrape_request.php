<?php

require_once __DIR__ . '/../../bootstrap.php';

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
