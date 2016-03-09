<?php

require_once 'bootstrap.php';

$app->get('/venue/:id', function ($id) use ($app, $entityManager) {
  $venue = $entityManager->find('\PF\Venue', $id);

  if (empty($venue)) {
    $app->notFound();
  }

  $res = $app->response();

  $res['Content-Type'] = 'application/json';
  $app->render('venue.json', array('venue' => $venue));
});

$app->post('/venue', function () use ($app, $entityManager) {
  $data = json_decode($app->request->getBody(), true);

  $venue = new \PF\Venue($data);

  $entityManager->persist($venue);
  $entityManager->flush();

  $app->render('message.json', array('message' => "Created Venue with ID " . $venue->getId()));
});

$app->get('/venues', function () use ($app, $entityManager) {
  $n = $app->request()->get('n');

  $venues = $entityManager->getRepository('\PF\Venue')->getVenues($n);

  $res['Content-Type'] = 'application/json';
  $app->render('venues.json', array('venues' => $venues));
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
