<?php

function stats_route($entityManager) {
  $stats = array();

  $createdData = $entityManager->getRepository('\PF\Venue')->getCreatedStats(365 * 2);

  $createdStats = array(
    'type' => 'Line',
    'title' => 'New Venues',
    'data' => array(),
    'labels' => array(),
  );

  foreach ($createdData as $item) {
    $createdStats['data'][] = $item['total'];
    $createdStats['labels'][] = $item['month'];
  }

  $stats['createdStats'] = $createdStats;

  $updatedData = $entityManager->getRepository('PF\Venue')->getUpdatedStats(365 * 2);

  $updatedStats = array(
    'type' => 'Line',
    'title' => 'Updates',
    'data' => array(),
    'labels' => array(),
  );

  foreach ($updatedData as $item) {
    $updatedStats['data'][] = $item['total'];
    $updatedStats['labels'][] = $item['month'];
  }

  $stats['updatedStats'] = $updatedStats;

  $freshnessData = $entityManager->getRepository('\PF\Venue')->getFreshnessStats();

  $freshnessStats = array(
    'type' => 'Pie',
    'title' => 'Freshness',
    'data' => array(),
  );

  foreach ($freshnessData as $item) {
    $freshnessStats['data'][] = array(
      'label' => $item['freshness'],
      'value' => $item['total'],
    );
  }

  $stats['freshnessStats'] = $freshnessStats;

  $requestStatsData = $entityManager->getRepository('PF\StatRecord')->getRequestStats(365 * 2);

  $requestStats = array(
    'type' => 'Line',
    'title' => 'Search',
    'data' => array(),
    'labels' => array(),
  );

  foreach ($requestStatsData as $item) {
    $requestStats['data'][] = $item['total'];
    $requestStats['labels'][] = $item['month'];
  }

  $stats['requestStats'] = $requestStats;

  return $stats;
}

$app->get('/stats', function () use ($app, $entityManager) {
  $stats = stats_route($entityManager);

  $app->responseData = array('stats' => $stats);
});
