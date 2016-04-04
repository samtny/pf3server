<?php

$app->get('/stats', function () use ($app, $entityManager) {
  $stats = array();

  $createdData = $entityManager->getRepository('\PF\Venue')->getCreatedStats();

  $createdStats = array(
    'title' => 'New Venues',
    'data' => array(),
    'labels' => array(),
  );

  foreach ($createdData as $item) {
    $createdStats['data'][] = $item['total'];
    $createdStats['labels'][] = $item['month'];
  }

  $stats[] = $createdStats;

  $updatedData = $entityManager->getRepository('PF\Venue')->getUpdatedStats();

  $updatedStats = array(
    'title' => 'Updates',
    'data' => array(),
    'labels' => array(),
  );

  foreach ($updatedData as $item) {
    $updatedStats['data'][] = $item['total'];
    $updatedStats['labels'][] = $item['month'];
  }

  $stats[] = $updatedStats;

  $app->responseData = array('stats' => $stats);
});
