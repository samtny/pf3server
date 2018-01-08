<?php

use PF\RequestProxy;

define('PF3_APP_LOG_FILE_GLOB', '/*.{log,0}');

$app->group('/app', function () use ($adminRouteMiddleware, $app, $entityManager, $config, $logger) {
  $app->get('/home', array($adminRouteMiddleware, 'call'), function () use ($app, $entityManager) {
    $stats = stats_route($entityManager);

    $recent_venues = venue_route_search($entityManager, new RequestProxy(array(
      'l' => 10,
    )));

    $unapproved_venues = venue_route_search($entityManager, new RequestProxy(array(
      'l' => 1000,
      's' => 'NEW',
    )));

    $unapproved_comments = comment_route_search($entityManager, new RequestProxy(array(
      'l' => 10,
      's' => 'NEW',
    )));

    $notifications = notification_route_search($entityManager, new RequestProxy());

    $flagged_venues = venue_route_search($entityManager, new RequestProxy(array(
      'l' => 10,
      's' => 'FLAGGED',
    )));

    $app->responseData = array(
      'recent_venues' => $recent_venues,
      'unapproved_venues' => $unapproved_venues,
      'unapproved_comments' => $unapproved_comments,
      'stats' => $stats,
      'notifications' => $notifications,
      'flagged_venues' => $flagged_venues,
    );
  });

  $app->post('/log', function () use ($app, $logger) {
    $json_log_encoded = $app->request->getBody();

    $json_log_decoded = json_decode($json_log_encoded, true);

    $level = !empty($json_log_decoded['level']) ? $json_log_decoded['level'] : 'info';

    $logger->{$level}($json_log_decoded['message'], array('raw' => $json_log_encoded));

    $app->status(201);

    $app->responseMessage = 'Created Log Entry';
  });

  $app->get('/logs', array($adminRouteMiddleware, 'call'), function () use ($app, $config) {
    $logs = array();

    $log_dir = $config['pf3server_log_directory'];

    foreach (glob($log_dir . PF3_APP_LOG_FILE_GLOB, GLOB_BRACE) as $log_file) {
      $logs[] = array(
        'filename' => $log_file,
        'size' => filesize($log_file),
        'hash' => md5($log_file),
        'date' => date("F d Y H:i:s.", filemtime($log_file)),
      );
    }

    $app->responseData = array(
      'logs' => $logs,
    );
  });

  $app->get('/logs/:hash', array($adminRouteMiddleware, 'call'), function ($hash) use ($app, $config) {
    $log = NULL;

    $log_dir = $config['pf3server_log_directory'];

    foreach (glob($log_dir . PF3_APP_LOG_FILE_GLOB, GLOB_BRACE) as $log_file) {
      if (md5($log_file) === $hash) {
        $size = filesize($log_file);

        $handle = fopen($log_file, 'r');

        $maxlength = 1000000;
        $offset = 0;

        $data = stream_get_contents($handle, $maxlength, $offset);

        $log = array(
          'filename' => $log_file,
          'size' => $size,
          'hash' => $hash,
          'date' => date("F d Y H:i:s.", filemtime($log_file)),
          'contents' => $data,
        );
      }
    }

    $app->responseData = array(
      'log' => $log,
    );
  });
});
