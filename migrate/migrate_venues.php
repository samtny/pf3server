<?php

require __DIR__ .  '/../bootstrap.php';

$entityManager = Bootstrap::getEntityManager();

echo "Migrating venues\n";

$batch_size = 100;

/**
 * @param $xml
 * @param \Doctrine\ORM\EntityManager $entityManager
 * @param bool $approve
 * @return int
 */
function parse_pf_xml($xml, $entityManager, $approve = true) {
  global $batch_size;

  $num = 0;

  foreach ($xml->locations->loc as $loc_index => $loc) {
    $venue = $entityManager->getRepository('\PF\Venue')->findOneBy(array('legacy_key' => $loc['key']));

    if (empty($venue)) {
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
      $venue->setCreated(date_create_from_format('Y-m-d', $loc->created));
      $venue->setUpdated(date_create_from_format('Y-m-d', $loc->date));
      $venue->setLegacyKey($loc['key']);

      foreach ($loc->game as $locmachine) {
        $ipdb = $locmachine->ipdb;

        switch ((string) $ipdb) {
          case '4652':
            $ipdb = 1267;

            break;
        }

        $game = $entityManager->getRepository('\PF\Game')->findOneBy(array('id' => $ipdb));

        if (!empty($game)) {
          if ($locmachine['rare'] == '1') {
            $game->setRare(true);
            $entityManager->persist($game);
          }

          if ($locmachine['new'] == '1') {
            $game->setNew(true);
            $entityManager->persist($game);
          }

          $machine = new \PF\Machine();

          $machine->setCondition($locmachine->cond);
          $machine->setPrice($locmachine->price);

          $machine->setGame($game);

          $venue->addMachine($machine, true);
        } else {
          echo "missing game: " . $locmachine['key'];
        }
      }

      foreach ($loc->comment as $loccomment) {
        $comment = new \PF\Comment();

        $comment->setText($loccomment->ctext);

        $comment->approve();

        $venue->addComment($comment, true);
      }

      if ($approve === true) {
        $venue->approve(true);
      }

      $entityManager->persist($venue);

      $num++;

      if ($num % $batch_size === 0) {
        $entityManager->flush();

        $entityManager->clear();
      }
    }

    $venue = NULL;
  }

  $entityManager->flush();

  return $num;
}

$l = !empty($argv[1]) ? $argv[1] : "10000";

$pf2data = file_get_contents("http://pinballfinder.org/pf2/pf?l=" . $l);

$xml = simplexml_load_string($pf2data);

$num = parse_pf_xml($xml, $entityManager);

echo "Generated " . $num . ' venues' . "\n";

$pf2data = file_get_contents("http://pinballfinder.org/pf2/pf?t=mgmt&q=unapproved&l=" . $l);

$xml = simplexml_load_string($pf2data);

$num = parse_pf_xml($xml, $entityManager, false);

echo "Generated " . $num . ' unapproved venues' . "\n";
