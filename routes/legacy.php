<?php

include __DIR__ . '/../src/pf2server/pf-class.php';

$app->group('/legacy', function () use ($app, $entityManager) {
  $app->get('/', function () use ($app, $entityManager) {
    /*
    $q = $_GET["q"]; // query
    $t = $_GET["t"]; // query type (venue, game, gamelist, special)
    $n = $_GET["n"]; // near
    $l = $_GET["l"]; // limit
    $p = $_GET["p"]; // options (minimal)
    $o = $_GET["o"]; // order
    $f = $_GET["f"]; // format (xml, json)
    */
    $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($app->request());

    $legacy_result = new Result();

    foreach ($venuesIterator as $venue) {
      $legacy_venue = new Venue();

      $legacy_venue->id = $venue->getId();
      $legacy_venue->name = $venue->getName();
      $legacy_venue->street = $venue->getStreet();
      $legacy_venue->city = $venue->getCity();
      $legacy_venue->state = $venue->getState();
      $legacy_venue->zipcode = $venue->getZipcode();
      $legacy_venue->phone = $venue->getPhone();
      $legacy_venue->lat = $venue->getLatitude();
      $legacy_venue->lon = $venue->getLongitude();
      $legacy_venue->updated = $venue->getUpdated()->format('Y-m-d');
      $legacy_venue->created = $venue->getCreated()->format('Y-m-d');
      $legacy_venue->url = $venue->getUrl();

      foreach ($venue->getActiveMachines() as $machine) {
        $legacy_game = new Game();

        $legacy_game->id = $machine->getId();

        $abbr = $machine->getGame()->getAbbreviation();

        $legacy_game->abbr = $abbr;

        $legacy_result->meta->gamedict->en[$abbr] = $machine->getGame()->getName();

        $legacy_game->cond = $machine->getCondition();
        $legacy_game->price = $machine->getPrice();
        $legacy_game->ipdb = $machine->getIpdb();
        $legacy_game->new = $machine->getGame()->getNew();
        $legacy_game->rare = $machine->getGame()->getRare();

        $legacy_venue->addGame($legacy_game);
      }

      foreach ($venue->getActiveComments() as $comment) {
        $legacy_comment = new Comment();

        $legacy_comment->id = $comment->getId();

        $legacy_comment->text = $comment->getText();
        $legacy_comment->date = $comment->getCreated()->format('c');

        $legacy_venue->addComment($legacy_comment);
      }

      $legacy_result->addVenue($legacy_venue);
    }

    if (!empty($legacy_result->meta->gamedict->en)) {
      asort($legacy_result->meta->gamedict->en);
    }

    $legacy_status = new Status();
    $legacy_status->status = 'success';
    $legacy_result->status = $legacy_status;

    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    //header('Access-Control-Allow-Origin: *');

    header('Content-type: application/xml');

    echo $legacy_result->saveXML();

    exit;
  });

  $app->post('/', function () use ($app, $entityManager) {
    $xml = $app->request->post('doc');

    $legacy_request = new Request();

    $legacy_request->loadXML($xml);

    foreach ($legacy_request->venues as $legacy_venue) {
      $is_new_venue = empty($legacy_venue->id);

      $venue = NULL;

      if ($is_new_venue) {
        $venue = new \PF\Venue();
      } else {
        $venue = $entityManager->find('PF\Venue', $legacy_venue->id);
      }

      if (!empty($venue)) {
        $venue->touch();

        $venue->setName($legacy_venue->name);
        $venue->setStreet($legacy_venue->street);
        $venue->setCity($legacy_venue->city);
        $venue->setState($legacy_venue->state);
        $venue->setZipcode($legacy_venue->zipcode);
        $venue->setLatitude($legacy_venue->lat);
        $venue->setLongitude($legacy_venue->lon);
        $venue->setPhone($legacy_venue->phone);
        $venue->setUrl($legacy_venue->url);

        //$flag = $legacy_venue->flag;

        foreach ($legacy_venue->games as $legacy_machine) {
          $machine = NULL;

          if (!empty($legacy_machine->id)) {
            $machine = $entityManager->find('PF\Machine', $legacy_machine->id);

            $machine->setCondition($legacy_machine->cond);
            $machine->setPrice($legacy_machine->price);

            if ($legacy_machine->deleted) {
              $entityManager->remove($machine);
            } else {
              $entityManager->persist($machine);
            }
          } else {
            $game = $entityManager->getRepository('\PF\Game')->findOneBy(array('abbreviation' => $legacy_machine->abbr));

            if (!empty($game)) {
              $machine = new \PF\Machine();

              $machine->setCondition($legacy_machine->cond);
              $machine->setPrice($legacy_machine->price);
              $machine->setGame($game);

              $venue->addMachine($machine);
            }
          }
        }

        foreach ($legacy_venue->comments as $legacy_comment) {
          if (empty($legacy_comment->id)) {
            $comment = new \PF\Comment();

            $comment->setText($legacy_comment->text);

            $venue->addComment($comment);
          }
        }

        $entityManager->persist($venue);

        $entityManager->flush();
      }
    }

    $legacy_result = new Result();

    $legacy_status = new Status();
    $legacy_status->status = 'success';

    $legacy_result->status = $legacy_status;

    header('HTTP/1.1 200 OK');

    $legacy_result_xml = $legacy_result->saveXML();

    header('Content-Length: ' . strlen($legacy_result_xml));
    header('Content-Type: application/xml;type=result;charset="utf-8"');

    echo $legacy_result_xml;

    exit;
  });
});
