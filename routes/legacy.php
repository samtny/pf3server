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

      foreach ($venue->getMachines() as $machine) {
        $legacy_game = new Game();

        $legacy_game->id = $machine->getId();
        $legacy_game->abbr = $machine->getName();

        $legacy_venue->addGame($legacy_game);
      }

      $result->addVenue($legacy_venue);
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
