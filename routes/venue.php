<?php

use JMS\Serializer\DeserializationContext;

function venue_route_search($entityManager, $params) {

  $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($params);

  $venues = [];

  foreach ($venuesIterator as $venue) {
    $venues[] = $venue;
  }

  return $venues;

}

$app->group('/venue', function () use ($entityManager, $logger, $serializer) {

  $this->get('/search', function ($request, $response) use ($entityManager, $logger) {

    $params = $request->getQueryParams();

    $logger->info('Venue request params', array('params' => $params));

    $venues = venue_route_search($entityManager, $params);

    $response->setPinfinderData([
      'count' => count($venues),
      'venues' => $venues,
    ]);

    return $response;

  });

  $this->get('/{id}', function ($request, $response, $args) use ($entityManager) {

    $venue = $entityManager->getRepository('\PF\Venue')->find($args['id']);

    if (empty($venue)) {
      $response = $response->withStatus(404);
    }
    else {
      $response->setPinfinderData([
        'venue' => $venue,
      ]);
    }

    return $response;

  });

  $this->post('/{id}/approve', function ($request, $response, $args) use ($entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($args['id']);

    if (empty($venue)) {
      $response = $response->withStatus(404);
    }
    else {
      $venue->approve();
      $entityManager->persist($venue);

      if (!empty($venue->getCreatedUser())) {
        $notification = new \PF\Notification();

        $notification->setUser($venue->getCreatedUser());
        $notification->setMessage('The venue \'' . $venue->getName() . '\' you added was approved!  Thank you!  -The Pinfinder Team');
        $notification->setQueryParams('q=' . $venue->getId());

        $entityManager->persist($notification);
      }

      $entityManager->flush();

      $response->setPinfinderMessage('Approved Venue with ID ' . $venue->getId());
    }

    return $response;

  })->add(new \PF\Middleware\PinfinderAdminRouteMiddleware());

  $this->post('/{id}/confirm', function ($request, $response, $args) use ($entityManager, $logger) {
    $logger->info('Venue confirm request', array('id' => $args['id']));

    /**
     * @var $venue \PF\Venue
     */
    $venue = $entityManager->getRepository('\PF\Venue')->find($args['id']);

    if (empty($venue)) {
      $response = $response->withStatus(404);
    }
    else {
      $venue->touch();
      $entityManager->persist($venue);

      $entityManager->flush();

      $response->setPinfinderMessage('Confirmed Venue with ID ' . $venue->getId());
    }

    return $response;
  });

  $this->post('/{id}/flag', function ($request, $response, $args) use ($entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($args['id']);

    if (empty($venue)) {
      $response = $response->withStatus(404);
    }
    else {
      $venue->flag();
      $entityManager->persist($venue);

      $entityManager->flush();

      $response->setPinfinderMessage('Flagged Venue with ID ' . $venue->getId());
    }

    return $response;
  });

  $this->delete('/{id}', function ($request, $response, $args) use ($entityManager) {
    $venue = $entityManager->getRepository('\PF\Venue')->find($args['id']);

    if (empty($venue)) {
      $response = $response->withStatus(404);
    }
    else {
      foreach ($venue->getMachines() as $machine) {
        $machine->touch();
        $machine->delete();

        $entityManager->persist($machine);
      }

      foreach ($venue->getComments() as $comment) {
        $comment->touch();
        $comment->delete();

        $entityManager->persist($comment);
      }

      $venue->touch();
      $venue->delete();

      $entityManager->persist($venue);

      $entityManager->flush();

      $response->setPinfinderMessage('Deleted Venue with ID ' . $venue->getId());
    }

    return $response;
  });

  $this->post('', function ($request, $response, $args) use ($entityManager, $serializer) {
    $json_venue_encoded = $request->getBody();

    $json_venue_decoded = json_decode($json_venue_encoded, true);

    $is_new_venue = empty($json_venue_decoded['id']);

    $venue_deserialization_context = DeserializationContext::create();
    $venue_deserialization_context->setGroups($is_new_venue ? array('create') : array('update'));

    $venue = $serializer->deserialize($json_venue_encoded, 'PF\Venue', 'json', $venue_deserialization_context);

    if (!$is_new_venue) {
      $venue->touch();
    }

    if (!empty($json_venue_decoded['machines'])) {
      foreach ($json_venue_decoded['machines'] as $json_machine_decoded) {
        $json_machine_encoded = json_encode($json_machine_decoded);

        $is_new_machine = empty($json_machine_decoded['id']);

        $machine_deserialization_context = DeserializationContext::create();
        $machine_deserialization_context->setGroups($is_new_machine ? array('create') : array('update'));

        $machine = $serializer->deserialize($json_machine_encoded, 'PF\Machine', 'json', $machine_deserialization_context);

        $game = NULL;

        if (!empty($json_machine_decoded['ipdb'])) {
          $game = $entityManager->getRepository('\PF\Game')->find($json_machine_decoded['ipdb']);
        } else {
          $game = $entityManager->getRepository('\PF\Game')->findOneBy(array('name' => $json_machine_decoded['name']));
        }

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

      $response = $response->withStatus($is_new_venue ? 201 : 200);

      $response->setPinfinderMessage(($is_new_venue ? 'Created Venue with ID ' : 'Updated Venue with ID ') . $venue->getId());
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $response = $response->withStatus(404);
    }

    return $response;
  });

});
