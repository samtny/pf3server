<?php

require_once 'bootstrap.php';

$app->group('/venue', function () use ($app, $entityManager, $venueDeserializer) {
  $app->get('/search', function () use ($app, $entityManager) {
    $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($app->request());

    $venues = [];

    foreach ($venuesIterator as $venue) {
      $venues[] = $venue;
    }

    $app->responseData = array('count' => count($venues), 'venues' => $venues);
  });

  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $venue = $entityManager->find('\PF\Venue', $id);

    if (empty($venue)) {
      $app->notFound();
    }

    $app->responseData = array('venue' => $venue);
  });

  $app->post('/', function () use ($app, $entityManager, $venueDeserializer) {
    $venue = $venueDeserializer->deserialize($app->request->getBody());

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
    $venue = $entityManager->find('\PF\Venue', $id);

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
