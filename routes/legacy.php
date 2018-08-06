<?php

use PF\Legacy;
use PF\Middleware\PinfinderAdminRouteMiddleware;
use PF\Middleware\PinfinderRequestStatsMiddleware;

$app->group('/pf2/pf', function () use ($app, $entityManager, $logger) {

  $this->get('', function ($request, $response) use ($entityManager, $logger) {

    $legacy_request = $request;

    $legacy_params = $legacy_request->getQueryParams();

    $logger->info('Legacy venue search request', array('params' => $legacy_params));

    $params = [];

    /*
        $q = $_GET["q"]; // query
        $t = $_GET["t"]; // query type (venue, game, gamelist, special)
        $n = $_GET["n"]; // near
        $l = $_GET["l"]; // limit
        $p = $_GET["p"]; // options (minimal)
        $o = $_GET["o"]; // order
        $f = $_GET["f"]; // format (xml, json)
    */

    $params['n'] = $legacy_params['n'];
    $params['l'] = $legacy_params['l'];

    if ($legacy_params['t'] === 'special') {
      switch ($legacy_params['q']) {
        case 'newgame':
          $params['x'] = 'new';

          break;
        case 'raregame':
          $params['x'] = 'rare';

          break;
        case 'recent':

          break;
        case 'museum':
        case 'upcomingtournaments':
        case 'mecca':
        default:
          $params['x'] = $legacy_params['q'];

          break;
      }
    }

    if ($legacy_params['t'] === 'game' && !empty($legacy_params['q'])) {
      $params['g'] = $legacy_params['q'];
    }

    if (!empty($legacy_params['q']) && ($legacy_params['t'] === 'venue' || is_numeric($legacy_params['q']))) {
      $params['q'] = $legacy_params['q'];
    }

    $venueIterator = $entityManager->getRepository('\PF\Venue')->getVenues($params, Doctrine\ORM\Query::HYDRATE_ARRAY);

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

  $this->post('', function ($request, $response, $args) use ($entityManager) {
    $xml = $request->getParsedBodyParam('doc');

    $legacy_request = new PF\Legacy\Request();

    $legacy_request->loadXML($xml);

    $user = NULL;

    if (!empty($legacy_request->user)) {
      $legacy_user = $legacy_request->user;

      if (!empty($legacy_user->tokens)) {
        $legacy_token = $legacy_user->tokens[0];

        $legacy_service = $legacy_token->service;
        $legacy_token_string = $legacy_token->token;

        if (!empty($legacy_service) && !empty($legacy_token_string)) {
          $tokenApp = $legacy_service;
          $tokenString = preg_replace('/\s|<|>/', '', $legacy_token_string);

          switch ($tokenApp) {
            case 'apns':
            case 'apnsfree':
            case 'apnsfree2':
              $tokenApp = 'apnsfree';

              break;
            default:
              $tokenApp = 'apnspro';

              break;
          }

          $token = $entityManager->getRepository('PF\Token')->findOneBy(array('app' => $tokenApp, 'token' => $tokenString));

          if (empty($token)) {
            $token = new PF\Token();

            $token->setApp($tokenApp);
            $token->setToken($tokenString);

            $user = new PF\User();

            $user->addToken($token);

            $entityManager->persist($user);
          } else {
            $user = $token->getUser();
          }
        }
      }
    }

    foreach ($legacy_request->venues as $legacy_venue) {
      $is_new_venue = empty($legacy_venue->id);

      $venue = NULL;

      if ($is_new_venue) {
        $venue = new \PF\Venue();

        $venue->setCreatedUser($user);
      } else {
        $venue = $entityManager->find('PF\Venue', $legacy_venue->id);
      }

      if (!empty($venue)) {
        $venue->touch();

        !empty($legacy_venue->name) && $venue->setName($legacy_venue->name);
        !empty($legacy_venue->street) && $venue->setStreet($legacy_venue->street);
        !empty($legacy_venue->city) && $venue->setCity($legacy_venue->city);
        !empty($legacy_venue->state) && $venue->setState($legacy_venue->state);
        !empty($legacy_venue->zipcode) && $venue->setZipcode($legacy_venue->zipcode);
        !empty($legacy_venue->lat) && $venue->setLatitude($legacy_venue->lat);
        !empty($legacy_venue->lon) && $venue->setLongitude($legacy_venue->lon);
        !empty($legacy_venue->phone) && $venue->setPhone($legacy_venue->phone);
        !empty($legacy_venue->url) && $venue->setUrl($legacy_venue->url);

        foreach ($legacy_venue->games as $legacy_machine) {
          $machine = NULL;

          if (!empty($legacy_machine->id)) {
            $machine = $entityManager->find('PF\Machine', $legacy_machine->id);

            !empty($legacy_machine->cond) && $machine->setCondition($legacy_machine->cond);
            !empty($legacy_machine->price) && $machine->setPrice($legacy_machine->price);

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

        if (!empty($legacy_venue->flag)) {
          $venue->flag();
        }

        $entityManager->persist($venue);
      }
    }

    $entityManager->flush();

    $legacy_result = new PF\Legacy\Result();

    $legacy_status = new PF\Legacy\Status();
    $legacy_status->status = 'success';

    $legacy_result->status = $legacy_status;

    header('HTTP/1.1 200 OK');

    $legacy_result_xml = $legacy_result->saveXML();

    header('Content-Length: ' . strlen($legacy_result_xml));
    header('Content-Type: application/xml;type=result;charset="utf-8"');

    echo $legacy_result_xml;

    return $response->withStatus(200);
  });

  $this->post('/gamedict/refresh', function ($request, $response, $next) use ($entityManager) {

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
