<?php

require_once 'bootstrap.php';

$app->get('/venue/:id', function ($id) use ($app, $entityManager, $serializer) {
  $venue = $entityManager->find('\PF\Venue', $id);

  if (empty($venue)) {
    $app->notFound();
  }

  $res = $app->response();

  $res['Content-Type'] = 'application/json';

  echo $serializer->serialize($venue, 'json');

  //echo json_encode($venue);
});

$app->post('/venue', function () use ($app, $entityManager) {
  $data = json_decode($app->request->getBody(), true);

  $venue = new \PF\Venue($data);

  $entityManager->persist($venue);
  $entityManager->flush();

  $app->render('message.json', array('message' => "Created Venue with ID " . $venue->getId()));
});

$app->get('/venues', function () use ($app, $entityManager, $serializer) {
  $n = $app->request()->get('n');

  $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($n);

  echo $serializer->serialize($venuesIterator, 'json');return;

  $venues = [];

  foreach ($venuesIterator as $venue) {
    $venues[] = $venue;
  }

  $res = $app->response();

  $res['Content-Type'] = 'application/json';

  $data = array(
    'venues' => $venues
  );

  $response = array(
    'meta' => array(
      'memory_get_peak_usage' => memory_get_peak_usage()
    ),
    'data' => $data,
  );

  echo json_encode($response);
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
