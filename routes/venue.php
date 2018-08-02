<?php

function venue_route_search($entityManager, $params) {
  $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($params);

  $venues = [];

  foreach ($venuesIterator as $venue) {
    $venues[] = $venue;
  }

  return $venues;
}

$app->get('/venue/search', function ($request, $response, $args) use ($entityManager, $logger) {

  $params = $request->getQueryParams();

  $logger->info('Venue request params', array('params' => $params));

  $venues = venue_route_search($entityManager, $params);

  $response->setPinfinderData([
    'count' => count($venues),
    'venues' => $venues
  ]);

  return $response->withStatus(200);

});
