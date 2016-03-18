<?php

require __DIR__ .  '/../bootstrap.php';

$dom = new DOMDocument();
libxml_use_internal_errors(true);

$contents = file_get_contents("ipdb.html");

$dom->loadHTML('<?xml encoding="utf-8" ?>' . $contents);

// authenticate and then view-source:http://ipdb.org/lists.cgi?puid=23777&list=games
// <tr><td><a href="machine.cgi?gid=2819&puid=23777">!WOW!</a></td><td>Mills Novelty Company</td><td>March, 1932</td><td>1</td><td>ME</td><td></td></tr>

$num = 0;

foreach ($dom->getElementsByTagName("tr") as $tr) {
  $fields = $tr->getElementsByTagName("td");

  if ($fields->length === 6) {
    $href = $fields->item(0)->getElementsByTagName("a")->item(0)->getAttribute('href');

    $re = '/gid=([0-9]*)/';

    preg_match($re, $href, $matches);

    if (!empty($matches[1])) {
      $game = new \PF\Game();

      $game->setName($fields->item(0)->textContent);

      $game->setId($matches[1]);

      //$game->setAbbreviation($parts[0]);

      $entityManager->persist($game);

      $num++;
    }

  }
}

$entityManager->flush();

echo "Generated " . $num . ' games' . "\n";
