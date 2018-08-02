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
      'venues' => $venues
    ]);

    return $response;

  });

});
