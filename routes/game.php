<?php

use JMS\Serializer\DeserializationContext;

$app->group('/game', function () use ($app, $entityManager, $serializer) {
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

  $app->post('', function () use ($app, $entityManager, $serializer) {
    $json_game_encoded = $app->request->getBody();

    $json_game_decoded = json_decode($json_game_encoded, true);

    $is_new_game = empty($json_game_decoded['id']);

    $game_deserialization_context = DeserializationContext::create();
    $game_deserialization_context->setGroups($is_new_game ? array('create') : array('update'));

    $game = $serializer->deserialize($json_game_encoded, 'PF\Game', 'json', $game_deserialization_context);

    if ($is_new_game) {
      $game->setName($game->getName());
    }

    try {
      $entityManager->persist($game);

      $entityManager->flush();

      $app->status($is_new_game ? 201 : 200);

      $app->responseMessage = ($is_new_game ? 'Created Game with ID ' : 'Updated Game with ID ') . $game->getId();
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $app->notFound();
    }
  });
});
