<?php

use PF\Legacy;
use PF\Middleware\PinfinderAdminRouteMiddleware;
use PF\Middleware\PinfinderRequestStatsMiddleware;

$app->group('/pf2/pf', function () use ($app, $entityManager, $logger) {

  $app->get('/', function ($request, $response) use ($app, $entityManager, $logger) {

    $legacy_request = $request;

    $logger->info('Legacy venue search request', array('params' => $legacy_request->getQueryParams()));

    $legacy_request_proxy = new Legacy\LegacyRequestProxy();
    /*
    $q = $_GET["q"]; // query
    $t = $_GET["t"]; // query type (venue, game, gamelist, special)
    $n = $_GET["n"]; // near
    $l = $_GET["l"]; // limit
    $p = $_GET["p"]; // options (minimal)
    $o = $_GET["o"]; // order
    $f = $_GET["f"]; // format (xml, json)
    */

    $legacy_request_proxy->set('n', $legacy_request->getQueryParam('n'));
    $legacy_request_proxy->set('l', $legacy_request->getQueryParam('l'));

    if ($legacy_request->getQueryParam('t') === 'special') {
      switch ($legacy_request->getQueryParam('q')) {
        case 'newgame':
          $legacy_request_proxy->set('x', 'new');

          break;
        case 'raregame':
          $legacy_request_proxy->set('x', 'rare');

          break;
        case 'recent':

          break;
        case 'museum':
        case 'upcomingtournaments':
        case 'mecca':
        default:
          $legacy_request_proxy->set('x', $legacy_request->getQueryParam('q'));

          break;
      }
    }

    if ($legacy_request->getQueryParam('t') === 'game' && !empty($legacy_request->getQueryParam('q'))) {
      $legacy_request_proxy->set('g', $legacy_request->getQueryParam('q'));
    }

    if (!empty($legacy_request->getQueryParam('q')) && ($legacy_request->getQueryParam('t') === 'venue' || is_numeric($legacy_request->getQueryParam('q')))) {
      $legacy_request_proxy->set('q', $legacy_request->getQueryParam('q'));
    }

    $venueIterator = $entityManager->getRepository('\PF\Venue')->getVenues($legacy_request_proxy, Doctrine\ORM\Query::HYDRATE_ARRAY);

    $legacy_result = new PF\Legacy\Result();

    foreach ($venueIterator as $venue) {
      $legacy_venue = new PF\Legacy\Venue();

      $legacy_venue->id = $venue['id'];
      $legacy_venue->name = $venue['name'];
      $legacy_venue->street = $venue['street'];
      $legacy_venue->city = $venue['city'];
      $legacy_venue->state = $venue['state'];
      $legacy_venue->zipcode = $venue['zipcode'];
      $legacy_venue->phone = $venue['phone'];
      $legacy_venue->lat = $venue['latitude'];
      $legacy_venue->lon = $venue['longitude'];
      $legacy_venue->updated = date_format($venue['updated'], 'Y-m-d');
      $legacy_venue->created = date_format($venue['created'], 'Y-m-d');
      $legacy_venue->url = $venue['url'];

      foreach ($venue['machines'] as $machine) {
        $legacy_game = new PF\Legacy\Game();

        $legacy_game->id = $machine['id'];

        $abbr = $machine['game']['abbreviation'];

        $legacy_game->abbr = $abbr;

        $legacy_result->meta->gamedict->en[$abbr] = $machine['game']['name'];

        $legacy_game->cond = $machine['condition'];
        $legacy_game->price = $machine['price'];
        $legacy_game->ipdb = $machine['game']['id'];
        $legacy_game->new = $machine['game']['new'];
        $legacy_game->rare = $machine['game']['rare'];

        $legacy_venue->addGame($legacy_game);
      }

      foreach ($venue['comments'] as $comment) {
        $legacy_comment = new PF\Legacy\Comment();

        $legacy_comment->id = $comment['id'];

        $legacy_comment->text = $comment['text'];
        $legacy_comment->date = date_format($comment['created'], 'c');

        $legacy_venue->addComment($legacy_comment);
      }

      $legacy_result->addVenue($legacy_venue);
    }

    if (!empty($legacy_result->meta->gamedict->en)) {
      asort($legacy_result->meta->gamedict->en);
    }

    $legacy_status = new PF\Legacy\Status();
    $legacy_status->status = 'success';
    $legacy_result->status = $legacy_status;

    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/xml');

    echo $legacy_result->saveXML();

    return $response->withHeader('Content-Type', 'application/xml');

  })->add(new PinfinderRequestStatsMiddleware());

  $app->post('/gamedict/refresh', function ($request, $response, $next) use ($entityManager) {

    $params = [
      'l' => 999999,
    ];

    $gamesIterator = $entityManager->getRepository('\PF\Game')->getGames($params, $hydration_mode = Doctrine\ORM\Query::HYDRATE_ARRAY, 'name');

    $gameDict = "";

    foreach ($gamesIterator as $game) {
      if (!empty($game['abbreviation'])) {
        if (!empty($gameDict)) {
          $gameDict .= '\g';
        }

        $gameDict .= $game["abbreviation"] . '\f' . $game["name"] . '\f' . $game["ipdb"];
      }
    }

    file_put_contents(\Bootstrap::getConfig()['pf3server_gamedict_path'], $gameDict);

    $response = $next($request, $response);

    $response->setPinfinderMessage('Game dictionary refreshed');

    return $response;

  })->add(new PinfinderAdminRouteMiddleware());

});
