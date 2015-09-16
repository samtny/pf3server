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

$app->get('/venues', function () use ($app) {
  $dql = "SELECT v FROM \PF\Venue v ORDER BY v.id DESC";

  $query = $app->em->createQuery($dql);
  $query->setMaxResults(30);

  $venues = $query->getArrayResult();

  $res['Content-Type'] = 'application/json';
  $app->render('venues.json', array('venues' => $venues));
});

$app->run();
