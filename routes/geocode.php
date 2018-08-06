<?php

$app->group('/geocode', function () use ($app, $entityManager) {

  $this->get('', function ($request, $response, $args) use ($entityManager) {
    $geocode = $entityManager->getRepository('\PF\Geocode')->findOneBy(array('string' => $request->getQueryParam('address')));

    if (empty($geocode)) {
      $response = $response->withStatus(404);
    }
    else {
      $response->setPinfinderData([
        'geocode' => $geocode,
      ]);
    }

    return $response;
  });

})->add(new \PF\Middleware\PinfinderAdminRouteMiddleware());
