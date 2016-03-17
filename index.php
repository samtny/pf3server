<?php

require_once 'bootstrap.php';

use \PF\Venue;

$app->get('/venue/search', function () use ($app, $entityManager, $serializer) {
  $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($app->request());

  $venues = [];

  foreach ($venuesIterator as $venue) {
    $venues[] = $venue;
  }

  $app->responseData = array('count' => count($venues), 'venues' => $venues);
});

$app->get('/venue/:id', function ($id) use ($app, $entityManager) {
  $venue = $entityManager->find('\PF\Venue', $id);

  if (empty($venue)) {
    $app->notFound();
  }

  $app->responseData = array('venue' => $venue);
});

$app->post('/venue', function () use ($app, $entityManager, $serializer) {
  $deserializion_context = \JMS\Serializer\DeserializationContext::create()->setGroups(array('test'));

  $json_decoded_venue = json_decode($app->request->getBody(), true);

  if (!empty($json_decoded_venue['id'])) {
    $deserializion_context->setAttribute('target', $entityManager->getRepository('\PF\Venue')->find($json_decoded_venue['id']));
  } else {
    $deserializion_context->setAttribute('target', new Venue());
  }

  $deserialized_venue = $serializer->deserialize($app->request->getBody(), 'PF\Venue', 'json', $deserializion_context);

  //foreach($objectA as $k => $v) $objectB->$k = $v;

  if ($deserialized_venue->getId() != null) {
    $venue = $entityManager->merge($deserialized_venue);

    $entityManager->persist($venue);

    $entityManager->flush();

    $app->responseMessage = 'Updated Venue with ID ' . $venue->getId();
  } else {
    try {
      $venue = $entityManager->merge($deserialized_venue);

      $entityManager->persist($venue);

      $entityManager->flush();

      $app->responseMessage = 'Created Venue with ID ' . $venue->getId();
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $app->notFound();
    }
  }
});

$app->get('/game/:id', function ($id) use ($app, $entityManager) {
  $game = $entityManager->find('\PF\Game', $id);

  if (empty($game)) {
    $app->notFound();
  }

  $res = $app->response();

  $res['Content-Type'] = 'application/json';
  $app->render('game.json', array('game' => $game));
});

$app->post('/game', function() use ($app, $entityManager) {
  $data = json_decode($app->request->getBody(), true);

  $game = new \PF\Game($data);

  $entityManager->persist($game);
  $entityManager->flush();

  $app->render('message.json', array('message' => 'Created Game with ID ' . $game->getId()));
});

$app->run();
