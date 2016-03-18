<?php

require_once 'bootstrap.php';

use \PF\Venue;
use \PF\Machine;
use \PF\Comment;

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
  $json_venue_encoded = $app->request->getBody();

  $json_venue_decoded = json_decode($json_venue_encoded, true);

  $is_new_venue = empty($json_venue_decoded['id']);

  $venue_deserialization_context = \JMS\Serializer\DeserializationContext::create();

  if ($is_new_venue) {
    $venue_deserialization_context->setGroups(array('create'));
    $venue_deserialization_context->setAttribute('target', new Venue());
  } else {
    $venue_deserialization_context->setGroups(array('update'));
    $venue_deserialization_context->setAttribute('target', $entityManager->getRepository('\PF\Venue')->find($json_venue_decoded['id']));
  }

  $venue = $serializer->deserialize($json_venue_encoded, 'PF\Venue', 'json', $venue_deserialization_context);

  foreach ($json_venue_decoded['machines'] as $json_machine_decoded) {
    $json_machine_encoded = json_encode($json_machine_decoded);

    $is_new_machine = empty($json_machine_decoded['id']);

    $machine_deserialization_context = \JMS\Serializer\DeserializationContext::create(); 

    if ($is_new_machine) {
      $machine_deserialization_context->setGroups(array('create'));
      $machine_deserialization_context->setAttribute('target', new Machine());
    } else {
      $machine_deserialization_context->setGroups(array('update'));
      $machine_deserialization_context->setAttribute('target', $entityManager->getRepository('\PF\Machine')->find($json_machine_decoded['id']));
    }

    $machine = $serializer->deserialize($json_machine_encoded, 'PF\Machine', 'json', $machine_deserialization_context);

    $machine->setGame($entityManager->getRepository('\PF\Game')->find($machine->getIpdb()));

    $venue->addMachine($machine);
  }

  foreach ($json_venue_decoded['comments'] as $json_comment_decoded) {
    $json_comment_encoded = json_encode($json_comment_decoded);

    $is_new_comment = empty($json_comment_decoded['id']);

    $comment_deserialization_context = \JMS\Serializer\DeserializationContext::create();

    if ($is_new_comment) {
      $comment_deserialization_context->setGroups(array('create'));
      $comment_deserialization_context->setAttribute('target', new Comment());
    } else {
      $comment_deserialization_context->setGroups(array('update'));
      $comment_deserialization_context->setAttribute('target', $entityManager->getRepository('\PF\Comment')->find($json_comment_decoded['id']));
    }

    $comment = $serializer->deserialize($json_comment_encoded, 'PF\Comment', 'json', $comment_deserialization_context);

    $venue->addComment($comment);
  }
  
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
