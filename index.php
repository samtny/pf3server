<?php

require_once 'bootstrap.php';

$app->get('/venue/:id', function ($id) use ($app) {
  $venue = $app->em->find('\PF\Venue', $id);

  if (empty($venue)) {
    $app->notFound();
  }

  $res = $app->response();

  //$res['Content-Type'] = 'text/xml';
  //$app->render('pinfinderapp.xml', array('venues' => $venues));

  $res['Content-Type'] = 'application/json';
  $app->render('venue.json', array('venue' => $venue));
});

$app->post('/venue', function () use ($app) {
  $data = json_decode($app->request->getBody(), true);

  $venue = new \PF\Venue();
  $venue->setName($data['name']);

  $app->em->persist($venue);
  $app->em->flush();

  $app->render('message.json', array('message' => "Created Venue with ID " . $venue->getId()));
});

$app->get('/venues', function () use ($app) {
  $venues = $app->em->getRepository('\PF\Venue')->getRecentVenues();

  $res['Content-Type'] = 'application/json';
  $app->render('venues.json', array('venues' => $venues));
});

$app->run();
