<?php

use JMS\Serializer\DeserializationContext;

$app->group('/game', function () use ($adminRouteMiddleware, $app, $entityManager, $serializer) {
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

  $app->post('/:id/merge/:mergeId', array($adminRouteMiddleware, 'call'), function ($id, $mergeId) use ($app, $entityManager) {
    /**
     * @var $sourceGame \PF\Game
     * @var $targetGame \PF\Game
     */
    $sourceGame = $entityManager->find('\PF\Game', $id);
    $targetGame = $entityManager->find('\PF\Game', $mergeId);

    if (empty($sourceGame) || empty($targetGame)) {
      $app->status(500);

      $app->responseMessage = ('Invalid parameters received for merge');

      return;
    }

    $machinesIterator = $entityManager->getRepository('\PF\Machine')->findBy(array('game' => $sourceGame));

    $updates = 0;

    foreach ($machinesIterator as $machine) {
      /** @var $machine \PF\Machine **/
      $machine->setGame($targetGame);

      $entityManager->persist($machine);

      $updates += 1;
    }

    if (!empty($sourceGame->getName()) && !empty($targetGame->getName()) && $sourceGame->getName() != $targetGame->getName()) {
      $lookup = $entityManager->getRepository('\PF\GameLookup')->findOneBy(array('lookup_string' => $sourceGame->getName()));

      if (empty($lookup)) {
        $lookup = new \PF\GameLookup();
        $lookup->setLookupString($sourceGame->getName());
        $lookup->setGame($targetGame);

        $entityManager->persist($lookup);
      }
    }

    $entityManager->remove($sourceGame);

    $entityManager->flush();

    $app->status(201);

    $app->responseMessage = ('Game ' . $id . ' merged to ' . $mergeId . '.  Machines updated: ' . $updates);
  });

  $app->post('', array($adminRouteMiddleware, 'call'), function () use ($app, $entityManager, $serializer) {
    $json_game_encoded = $app->request->getBody();

    $json_game_decoded = json_decode($json_game_encoded, true);

    $is_new_game = empty($json_game_decoded['id']);

    $game_deserialization_context = DeserializationContext::create();
    $game_deserialization_context->setGroups($is_new_game ? array('create') : array('update'));

    $game = $serializer->deserialize($json_game_encoded, 'PF\Game', 'json', $game_deserialization_context);

    if ($is_new_game) {
      $game->setName($game->getName());
      $game->setIpdb($game->getIpdb());

      $abbreviation = \PF\Utilities\GameUtil::generateAbbreviation($game);

      $game->setAbbreviation($abbreviation);
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
