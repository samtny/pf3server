<?php

$app->group('/game', function () use ($app, $entityManager) {
  $app->get('/search', function () use ($app, $entityManager) {
    $gamesIterator = $entityManager->getRepository('\PF\Game')->getGames($app->request());

    $games = [];

    foreach ($gamesIterator as $game) {
      $games[] = $game;
    }

    $app->responseData = array('count' => count($games), 'games' => $games);
  });

  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $game = $entityManager->find('\PF\Game', $id);

    if (empty($game)) {
      $app->notFound();
    }

    $app->responseData = array('game' => $game);
  });
});
