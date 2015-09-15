<?php

require_once 'bootstrap.php';

$app->get('/venue/:id', function ($id) use ($app) {
  $venue = array(
    'id' => $id,
    'name' => 'Reciprocal Skateboards',
  );

  $venues = array($venue);

  $res = $app->response();

  //$res['Content-Type'] = 'text/xml';
  //$app->render('pinfinderapp.xml', array('venues' => $venues));

  $res['Content-Type'] = 'application/json';
  $app->render('pinfinderapp.json', array('venues' => $venues));
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
