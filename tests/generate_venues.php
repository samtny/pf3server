<?php

require __DIR__ .  '/../bootstrap.php';

$pf2data = file_get_contents("http://pinballfinder.org/pf2/pf");

$xml = simplexml_load_string($pf2data);

$num = 0;
foreach ($xml->locations->loc as $loc) {
  $venue = new \PF\Venue();

  $venue->setName($loc->name);
  $venue->setStreet($loc->addr);
  $venue->setCity($loc->city);
  $venue->setState($loc->state);
  $venue->setZipcode($loc->zipcode);
  $venue->setPhone($loc->phone);
  $venue->setUrl($loc->url);

  $entityManager->persist($venue);

  $num++;
}

$entityManager->flush();

echo "Generated " . $num . ' venues' . "\n";
