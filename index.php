<?php

require_once 'bootstrap.php';

$app->get('/venue/:id', function ($id) use ($app) {
  $venue = $app->em->find('\PF\Venue', $id);

  $venues = array($venue);

  $res = $app->response();

  //$res['Content-Type'] = 'text/xml';
  //$app->render('pinfinderapp.xml', array('venues' => $venues));

  $res['Content-Type'] = 'application/json';
  $app->render('venue.json', array('venue' => $venue));
});

$app->post('/venue', function () use ($app) {
  $newVenueName = $argv[1];

  $venue = new \PF\Venue();
  $venue->setName($newVenueName);

  $app->em->persist($venue);
  $app->em->flush();

  echo "Created Venue with ID " . $venue->getId() . "\n";
});

$app->run();
