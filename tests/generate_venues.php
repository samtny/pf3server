<?php

require __DIR__ .  '/../bootstrap.php';

$pf2data = file_get_contents("http://pinballfinder.org/pf2/pf?l=1000");

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
  $venue->setLatitude($loc->lat);
  $venue->setLongitude($loc->lon);

  foreach ($loc->game as $locmachine) {
    $machine = new \PF\Machine();

    $machine->setCondition($locmachine->cond);
    $machine->setPrice($locmachine->price);

    $game = $entityManager->getRepository('\PF\Game')->findOneBy(array('abbreviation' => $locmachine->abbr));

    $machine->setGame($game);

    $venue->addMachine($machine);
  }

  $venue->approve();

  $entityManager->persist($venue);

  $num++;
}

$entityManager->flush();

echo "Generated " . $num . ' venues' . "\n";
