<?php

require_once "../bootstrap.php";

$venueId = $argv[1];

$venue = $app->em->find('\PF\Venue', $venueId);

$venue->approve();

$app->em->persist($venue);
$app->em->flush();

echo "Approved Venue with ID " . $venue->getId() . "\n";
