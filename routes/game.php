<?php

use JMS\Serializer\DeserializationContext;

$app->group('/game', function () use ($entityManager, $serializer) {

  $this->get('/search', function ($request, $response, $args) use ($entityManager) {
    $gamesIterator = $entityManager->getRepository('\PF\Game')->getGames($request->getQueryParams());

    $games = [];

    foreach ($gamesIterator as $game) {
      $games[] = $game;
    }

    $response->setPinfinderData([
      'count' => count($games),
      'games' => $games,
    ]);

    return $response;
  });

  $this->get('/{id}', function ($request, $response, $args) use ($entityManager) {
    $game = $entityManager->find('\PF\Game', $args['id']);

    if (empty($game)) {
      $response = $response->withStatus(404);
    }
    else {
      $response->setPinfinderData([
        'game' => $game,
      ]);
    }

    return $response;
  });

  $this->post('/{id}/merge/{mergeId}', function ($request, $response, $args) use ($entityManager) {
    /**
     * @var $sourceGame \PF\Game
     * @var $targetGame \PF\Game
     */
    $sourceGame = $entityManager->find('\PF\Game', $args['id']);
    $targetGame = $entityManager->find('\PF\Game', $args['mergeId']);

    if (empty($sourceGame) || empty($targetGame)) {
      $response = $response->withStatus(500);

      $response->setPinfinderMessage('Invalid parameters received for merge');

      return $response;
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

    $response = $response->withStatus(201);

    $response->setPinfinderMessage('Game ' . $args['id'] . ' merged to ' . $args['mergeId'] . '.  Machines updated: ' . $updates);

    return $response;
  });

  $this->post('', function ($request, $response, $args) use ($entityManager, $serializer) {
    $json_game_encoded = $request->getBody();

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

      $response = $response->withStatus($is_new_game ? 201 : 200);

      $response->setPinfinderMessage(($is_new_game ? 'Created Game with ID ' : 'Updated Game with ID ') . $game->getId());
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $response = $response->withStatus(404);
    }

    return $response;
  });

})->add(new \PF\Middleware\PinfinderAdminRouteMiddleware());
