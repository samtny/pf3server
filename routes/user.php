<?php

$app->group('/user', function () use ($entityManager) {

  $this->get('', function ($request, $response, $args) use ($entityManager) {
    $session = $entityManager->getRepository('PF\Session')->find($_COOKIE['session']);

    if (empty($session)) {
      $response = $response->withStatus(404);
    }

    $response->setPinfinderData([
      'user' => $session->getUser(),
    ]);

    return $response;
  });

})->add(new \PF\Middleware\PinfinderAdminRouteMiddleware());
