<?php

use PF\RequestProxy;

$app->group('/app', function () use ($adminRouteMiddleware, $app, $entityManager) {
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
});
