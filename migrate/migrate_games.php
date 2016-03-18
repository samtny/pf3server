<?php

require __DIR__ .  '/../bootstrap.php';

$dom = new DOMDocument();
libxml_use_internal_errors(true);

$contents = file_get_contents("ipdb.html");

$dom->loadHTML('<?xml encoding="utf-8" ?>' . $contents);

// authenticate and then view-source:http://ipdb.org/lists.cgi?puid=23777&list=games
// <tr><td><a href="machine.cgi?gid=2819&puid=23777">!WOW!</a></td><td>Mills Novelty Company</td><td>March, 1932</td><td>1</td><td>ME</td><td></td></tr>

$new = 0;

foreach ($dom->getElementsByTagName("tr") as $tr) {
  $fields = $tr->getElementsByTagName("td");

  if ($fields->length === 6) {
    $href = $fields->item(0)->getElementsByTagName("a")->item(0)->getAttribute('href');

    $re = '/gid=([0-9]*)/';

    preg_match($re, $href, $matches);

    if (!empty($matches[1])) {
      $ipdb = $matches[1];

      $game = $entityManager->getRepository('\PF\Game')->find($ipdb);

      if (!empty($game)) {
        $game->setName($fields->item(0)->textContent);

        //$game->setAbbreviation($parts[0]);
      } else {
        $game = new \PF\Game();

        $game->setId($ipdb);

        $game->setName($fields->item(0)->textContent);

        //$game->setAbbreviation($parts[0]);

        $new++;
      }

      $entityManager->persist($game);
    }
  }
}

$entityManager->flush();

echo "Generated " . $new . ' new games' . "\n";
