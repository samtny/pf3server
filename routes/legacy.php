<?php

include __DIR__ . '/../src/pf2server/pf-class.php';

use JMS\Serializer\DeserializationContext;

$app->group('/legacy', function () use ($app, $entityManager, $serializer) {
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

    $result = new Result();

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

      foreach ($venue->getMachines() as $machine) {
        $legacy_game = new Game();

        $legacy_game->id = $machine->getId();

        $abbr = $machine->getGame()->getAbbreviation();

        $legacy_game->abbr = $abbr;

        $result->meta->gamedict->en[$abbr] = $machine->getGame()->getName();

        $legacy_game->cond = $machine->getCondition();
        $legacy_game->price = $machine->getPrice();
        $legacy_game->ipdb = $machine->getIpdb();

        $legacy_venue->addGame($legacy_game);
      }

      foreach ($venue->getComments() as $comment) {
        $legacy_comment = new Comment();

        $legacy_comment->id = $comment->getId();

        $legacy_comment->text = $comment->getText();
        $legacy_comment->date = $comment->getCreated()->format('c');

        $legacy_venue->addComment($legacy_comment);
      }

      $result->addVenue($legacy_venue);
    }

    if (!empty($result->meta->gamedict->en)) {
      asort($result->meta->gamedict->en);
    }

    $status = new Status();
    $status->status = 'success';
    $result->status = $status;

    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    //header('Access-Control-Allow-Origin: *');

    header('Content-type: application/xml');

    echo $result->saveXML();

    exit;
  });
});
