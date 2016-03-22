<?php

require_once 'bootstrap.php';

$app->group('/venue', function () use ($app, $entityManager, $serializer) {
  $app->get('/search', function () use ($app, $entityManager) {
    $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($app->request());

    $venues = [];

    foreach ($venuesIterator as $venue) {
      $venues[] = $venue;
    }

    $app->responseData = array('count' => count($venues), 'venues' => $venues);
  });

  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($id);

    if (empty($venue)) {
      $app->notFound();
    }

    $app->responseData = array('venue' => $venue);
  });

  $app->post('', function () use ($app, $entityManager, $serializer) {
    $json_venue_encoded = $app->request->getBody();

    $json_venue_decoded = json_decode($json_venue_encoded, true);

    $venue = $serializer->deserialize($json_venue_encoded, 'PF\Venue', 'json');

    if (!empty($json_venue_decoded['machines'])) {
      foreach ($json_venue_decoded['machines'] as $json_machine_decoded) {
        $json_machine_encoded = json_encode($json_machine_decoded);

        $machine = $serializer->deserialize($json_machine_encoded, 'PF\Machine', 'json');

        $game = $entityManager->getRepository('\PF\Game')->find($json_machine_decoded['ipdb']);

        $machine->setGame($game);

        $venue->addMachine($machine);
      }
    }

    if (!empty($json_venue_decoded['comments'])) {
      foreach ($json_venue_decoded['comments'] as $json_comment_decoded) {
        $json_comment_encoded = json_encode($json_comment_decoded);

        $comment = $serializer->deserialize($json_comment_encoded, 'PF\Comment', 'json');

        $venue->addComment($comment);
      }
    }

    $is_new_venue = empty($venue->getId());

    try {
      $entityManager->persist($venue);

      $entityManager->flush();

      $app->status($is_new_venue ? 201 : 200);

      $app->responseMessage = ($is_new_venue ? 'Created Venue with ID ' : 'Updated Venue with ID ') . $venue->getId();
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $app->notFound();
    }
  });

  $app->delete('/:id', function ($id) use ($app, $entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($id);

    if (empty($venue)) {
      $app->notFound();
    }

    $venue->delete();

    $entityManager->persist($venue);

    $entityManager->flush();

    $app->responseMessage = 'Deleted Venue with ID ' . $venue->getId();
  });
});

$app->group('/game', function () use ($app, $entityManager) {
  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $game = $entityManager->find('\PF\Game', $id);

    if (empty($game)) {
      $app->notFound();
    }

    $app->responseData = array('game' => $game);
  });
});

$app->run();
