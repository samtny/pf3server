<?php

require_once "../bootstrap.php";

$newVenueName = $argv[1];

$venue = new \PF\Venue();
$venue->setName($newVenueName);

$app->em->persist($venue);
$app->em->flush();

echo "Created Venue with ID " . $venue->getId() . "\n";
