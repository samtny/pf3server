<?php

require_once 'bootstrap.php';

require 'vendor/autoload.php';

$app = new \Slim\Slim(
  array(
    'mode' => 'development',
    'view' => new \Slim\Views\Twig(),
  )
);

$app->configureMode('development', function () use ($app) {
  $app->config(array(
    'cookies.lifetime' => 'Never',
    'debug' => true,
  ));
});

$app->configureMode('production', function () use ($app) {
  $app->config(array(
    'cookies.lifetime' => '2 Hours',
    'debug' => false,
  ));
});

$responseFormat = $app->request->get('f');

$app->get('/venue/:id', function ($id) use ($app) {
  $venue = array(
    'id' => $id,
    'name' => 'Reciprocal Skateboards',
  );

  $venues = array($venue);

  $res = $app->response();
  $res['Content-Type'] = 'text/xml';

  $app->render('pinfinderapp.xml', array('venues' => $venues));
});

$app->post('/venue', function () use ($app, $entityManager) {
  $newVenueName = $argv[1];

  $venue = new Venue();
  $venue->setName($newVenueName);

  $entityManager->persist($venue);
  $entityManager->flush();

  echo "Created Product with ID " . $venue->getId() . "\n";
});

$app->run();
