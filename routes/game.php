<?php

$app->group('/game', function () use ($app, $entityManager) {
  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $game = $entityManager->find('\PF\Game', $id);

    if (empty($game)) {
      $app->notFound();
    }

    $app->responseData = array('game' => $game);
  });
});
