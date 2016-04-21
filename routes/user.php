<?php

$app->group('/user', array($adminRouteMiddleware, 'call'), function () use ($app, $entityManager) {
  $app->get('/', function () use ($app, $entityManager) {
    $session = $entityManager->getRepository('PF\Session')->find($_COOKIE['session']);

    if (empty($session)) {
      $app->notFound();
    }

    $app->responseData = array('user' => $session->getUser());
  });
});
