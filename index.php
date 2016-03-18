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
  $json_venue_decoded = json_decode($app->request->getBody(), true);

  $is_new_venue = empty($json_venue_decoded['id']);

  if ($is_new_venue) {
    $deserializion_context = \JMS\Serializer\DeserializationContext::create()->setGroups(array('create'));
    $deserializion_context->setAttribute('target', new Venue());
  } else {
    $deserializion_context = \JMS\Serializer\DeserializationContext::create()->setGroups(array('update'));
    $deserializion_context->setAttribute('target', $entityManager->getRepository('\PF\Venue')->find($json_venue_decoded['id']));
  }

  $venue = $serializer->deserialize($app->request->getBody(), 'PF\Venue', 'json', $deserializion_context);

  try {
    $entityManager->persist($venue);

    $entityManager->flush();

    $app->responseMessage = ($is_new_venue ? 'Created Venue with ID ' : 'Updated Venue with ID ') . $venue->getId();
  } catch (\Doctrine\ORM\EntityNotFoundException $e) {
    $app->notFound();
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
