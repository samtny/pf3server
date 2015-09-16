<?php

require '../bootstrap.php';

$venues = array(
  array(
    'name' => 'Reciprocal Skateboards',
  ),
  array(
    'name' => 'High Dive',
  ),
  array(
    'name' => 'Pioneers Bar',
  ),
);

foreach ($venues as $newVenue) {
  $venue = new \PF\Venue();

  $venue->setName($newVenue['name']);

  $app->em->persist($venue);
}

$app->em->flush();

echo "Generated " . count($venues) . ' venues' . "\n";
