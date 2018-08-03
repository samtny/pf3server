<?php

function venue_route_search($entityManager, $params) {

  $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($params);

  $venues = [];

  foreach ($venuesIterator as $venue) {
    $venues[] = $venue;
  }

  return $venues;

}

$app->group('/venue', function () use ($entityManager, $logger) {

  $this->get('/search', function ($request, $response) use ($entityManager, $logger) {

    $params = $request->getQueryParams();

    $logger->info('Venue request params', array('params' => $params));

    $venues = venue_route_search($entityManager, $params);

    $response->setPinfinderData([
      'count' => count($venues),
      'venues' => $venues,
    ]);

    return $response;

  });

  $this->get('/{id}', function ($request, $response, $args) use ($entityManager) {

    $venue = $entityManager->getRepository('\PF\Venue')->find($args['id']);

    if (empty($venue)) {
      $response = $response->withStatus(404);
    }
    else {
      $response->setPinfinderData([
        'venue' => $venue,
      ]);
    }

    return $response;

  });

  $this->post('/{id}/approve', function ($request, $response, $args) use ($entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($args['id']);

    if (empty($venue)) {
      $response = $response->withStatus(404);
    }
    else {
      $venue->approve();
      $entityManager->persist($venue);

      if (!empty($venue->getCreatedUser())) {
        $notification = new \PF\Notification();

        $notification->setUser($venue->getCreatedUser());
        $notification->setMessage('The venue \'' . $venue->getName() . '\' you added was approved!  Thank you!  -The Pinfinder Team');
        $notification->setQueryParams('q=' . $venue->getId());

        $entityManager->persist($notification);
      }

      $entityManager->flush();

      $response->setPinfinderMessage('Approved Venue with ID ' . $venue->getId());
    }

    return $response;

  })->add(new \PF\Middleware\PinfinderAdminRouteMiddleware());

});
