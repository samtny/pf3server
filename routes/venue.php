<?php

use JMS\Serializer\DeserializationContext;

$app->group('/venue', function () use ($app, $entityManager, $serializer, $adminRouteMiddleware) {
    $app->get('/search', function () use ($app, $entityManager) {
        $venuesIterator = $entityManager->getRepository('\PF\Venue')->getVenues($app->request());

        $venues = [];

        foreach ($venuesIterator as $venue) {
            $venues[] = $venue;
        }

        $app->responseData = array('count' => count($venues), 'venues' => $venues);
    });

    $app->post('/:id/approve', array($adminRouteMiddleware, 'call'), function ($id) use ($app, $entityManager) {
        $venue = $entityManager->getRepository('\PF\Venue')->find($id);

        if (empty($venue)) {
            $app->notFound();
        }

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

        $app->responseMessage = 'Approved Venue with ID ' . $venue->getId();
    });

    $app->get('/:id', function ($id) use ($app, $entityManager) {
        $venue = $entityManager->getRepository('\PF\Venue')->find($id);

        if (empty($venue)) {
            $app->notFound();
        }

        $app->responseData = array('venue' => $venue);
    });

    $app->post('', function () use ($app, $entityManager, $serializer) {
        $json_venue_encoded = $app->request->getBody();

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
