<?php

if (extension_loaded('xhprof')) {
  include_once '/usr/share/php/xhprof_lib/utils/xhprof_lib.php';
  include_once '/usr/share/php/xhprof_lib/utils/xhprof_runs.php';
  xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

require_once 'bootstrap.php';

$app->get('/venue/:id', function ($id) use ($app, $entityManager) {
  $venue = $entityManager->find('\PF\Venue', $id);

  if (empty($venue)) {
    $app->notFound();
  }

  $res = $app->response();

  //$res['Content-Type'] = 'text/xml';
  //$app->render('pinfinderapp.xml', array('venues' => $venues));

  $res['Content-Type'] = 'application/json';
  $app->render('venue.json', array('venue' => $venue));
});

$app->post('/venue', function () use ($app, $entityManager) {
  $data = json_decode($app->request->getBody(), true);

  $venue = new \PF\Venue();
  $venue->setName($data['name']);

  $entityManager->persist($venue);
  $entityManager->flush();

  $app->render('message.json', array('message' => "Created Venue with ID " . $venue->getId()));
});

$app->get('/venues', function () use ($app, $entityManager) {
  $venues = $entityManager->getRepository('\PF\Venue')->getRecentVenues();

  $res['Content-Type'] = 'application/json';
  $app->render('venues.json', array('venues' => $venues));
});

$app->run();

if (extension_loaded('xhprof')) {
  $profiler_namespace = 'pf3server';  // namespace for your application
  $xhprof_data = xhprof_disable();
  $xhprof_runs = new XHProfRuns_Default();
  $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
}
