<?php

$app->group('/geocode', array($adminRouteMiddleware, 'call'), function () use ($app, $entityManager) {
  $app->get('', function () use ($app, $entityManager) {
    $geocode = $entityManager->getRepository('\PF\Geocode')->findOneBy(array('string' => $app->request()->params('address')));

    if (empty($geocode)) {
      $app->notFound();
    }

    $app->responseData = array('geocode' => $geocode);
  });
});
