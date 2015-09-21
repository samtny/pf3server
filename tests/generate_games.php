<?php

require __DIR__ .  '/../bootstrap.php';

$pf2data = file_get_contents('http://pinballfinder.org/gamedict.txt');

$games = explode('\g', $pf2data);

$num = 0;
foreach ($games as $data) {
  $game = new \PF\Game();

  $parts = explode('\f', $data);

  $game->setName($parts[1]);
  $game->setIpdb($parts[2]);
  $game->setAbbreviation($parts[0]);

  $entityManager->persist($game);

  $num++;
}

$entityManager->flush();

echo "Generated " . $num . ' games' . "\n";
