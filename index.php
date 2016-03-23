<?php

require_once 'bootstrap.php';

use JMS\Serializer\DeserializationContext;

$app->any('/login', function () use ($app, $entityManager) {
  $username = $app->request->post('username');
  $password = $app->request->post('password');

  $username_addslashes = addslashes($username);
  $password_md5 = md5($password);

  $user = $entityManager->getRepository('\PF\User')->findOneBy(array('username' => $username_addslashes, 'password' => $password_md5));

  if (empty($user)) {
    $app->render('login.html');
  } else {
    session_start();

    $_SESSION['username'] = $username_addslashes;
    $_SESSION['password'] = $password_md5;

    $session = $entityManager->getRepository('\PF\Session')->findOneBy(array('user' => $user));

    if (empty($session)) {
      $session = new \PF\Session();
      $session->setUser($user);

      $entityManager->persist($session);
    }

    setcookie("session", $session->getId(), time() + 3600 * 24 * 365);

    $app->redirect('/admin');
  }
});

$app->group('/admin', array($adminRouteMiddleware, 'call'), function () use ($app) {
  $app->get('', function () use ($app) {
    $app->render('admin.html');
  });
});

$app->group('/venue', function () use ($app, $entityManager, $serializer, $adminRouteMiddleware) {
  $app->get('/search', function () use ($app, $entityManager) {
    $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($app->request());

    $venues = [];

    foreach ($venuesIterator as $venue) {
      $venues[] = $venue;
    }

    $app->responseData = array('count' => count($venues), 'venues' => $venues);
  });

  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($id);

    if (empty($venue)) {
      $app->notFound();
    }

    $app->responseData = array('venue' => $venue);
  });

  $app->post('/:id/approve', array($adminRouteMiddleware, 'call'), function ($id) use ($app, $entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($id);

    if (empty($venue)) {
      $app->notFound();
    }

    $venue->approve();

    $entityManager->persist($venue);
    $entityManager->flush();

    $app->responseMessage = 'Approved Venue with ID ' . $venue->getId();
  });

  $app->post('', function () use ($app, $entityManager, $serializer) {
    $json_venue_encoded = $app->request->getBody();

    $json_venue_decoded = json_decode($json_venue_encoded, true);

    $is_new_venue = empty($json_venue_decoded['id']);

    $venue_deserialization_context = DeserializationContext::create();
    $venue_deserialization_context->setGroups($is_new_venue ? array('create') : array('update'));

    $venue = $serializer->deserialize($json_venue_encoded, 'PF\Venue', 'json', $venue_deserialization_context);

    if (!empty($json_venue_decoded['machines'])) {
      foreach ($json_venue_decoded['machines'] as $json_machine_decoded) {
        $json_machine_encoded = json_encode($json_machine_decoded);

        $is_new_machine = empty($json_machine_decoded['id']);

        $machine_deserialization_context = DeserializationContext::create();
        $machine_deserialization_context->setGroups($is_new_machine ? array('create') : array('update'));

        $machine = $serializer->deserialize($json_machine_encoded, 'PF\Machine', 'json', $machine_deserialization_context);

        $game = $entityManager->getRepository('\PF\Game')->find($json_machine_decoded['ipdb']);

        $machine->setGame($game);

        $venue->addMachine($machine);
      }
    }

    if (!empty($json_venue_decoded['comments'])) {
      foreach ($json_venue_decoded['comments'] as $json_comment_decoded) {
        $json_comment_encoded = json_encode($json_comment_decoded);

        $is_new_comment = empty($json_comment_decoded['id']);

        $comment_deserialization_context = DeserializationContext::create();
        $comment_deserialization_context->setGroups($is_new_comment ? array('create') : array('update'));

        $comment = $serializer->deserialize($json_comment_encoded, 'PF\Comment', 'json', $comment_deserialization_context);

        $venue->addComment($comment);
      }
    }

    try {
      $entityManager->persist($venue);

      $entityManager->flush();

      $app->status($is_new_venue ? 201 : 200);

      $app->responseMessage = ($is_new_venue ? 'Created Venue with ID ' : 'Updated Venue with ID ') . $venue->getId();
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $app->notFound();
    }
  });

  $app->delete('/:id', function ($id) use ($app, $entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($id);

    if (empty($venue)) {
      $app->notFound();
    }

    $venue->delete();

    $entityManager->persist($venue);

    $entityManager->flush();

    $app->responseMessage = 'Deleted Venue with ID ' . $venue->getId();
  });
});

$app->group('/game', function () use ($app, $entityManager) {
  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $game = $entityManager->find('\PF\Game', $id);

    if (empty($game)) {
      $app->notFound();
    }

    $app->responseData = array('game' => $game);
  });
});

$app->run();
