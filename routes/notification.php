<?php

use JMS\Serializer\DeserializationContext;

function notification_route_search($entityManager, $params) {
  $notificationsIterator = $entityManager->getRepository('\PF\Notification')->getNotifications($params);

  $notifications = [];

  foreach ($notificationsIterator as $notification) {
    $notifications[] = $notification;
  }

  return $notifications;
}

$app->group('/notification', function () use ($entityManager, $serializer) {

  $this->get('/search', function ($request, $response, $args) use ($entityManager) {
    $notifications = notification_route_search($entityManager, $request->getQueryParams());

    $response->setPinfinderData([
      'count' => count($notifications),
      'notifications' => $notifications,
    ]);

    return $response;
  });

  $this->get('/{id}', function ($request, $response, $args) use ($entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($args['id']);

    if (empty($notification)) {
      $response = $response->withStatus(404);
    }
    else {
      $response->setPinfinderData([
        'notification' => $notification,
      ]);
    }

    return $response;
  });

  $this->post('/all/send', function ($request, $response, $args) use ($entityManager) {
    $notificationsIterator = $entityManager->getRepository('\PF\Notification')->getPendingNotifications();

    $count = 0;

    $client = new PF\Notifications\NotificationClient($entityManager);

    foreach ($notificationsIterator as $notification) {
      $count += 1;

      $client->sendNotification($notification);

      $notification->archive();

      $entityManager->persist($notification);
    }

    $entityManager->flush();

    $response->setPinfinderMessage('Sent ' . $count . ' Notification(s)');

    return $response;
  });

  $this->post('/{id}/send', function ($request, $response, $args) use ($entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($args['id']);

    if (empty($notification)) {
      $response = $response->withStatus(404);
    }
    else {
      $client = new PF\Notifications\NotificationClient($entityManager);

      $client->sendNotification($notification);

      $notification->archive();
      $entityManager->persist($notification);

      $entityManager->flush();

      $response->setPinfinderMessage('Sent Notification with ID ' . $notification->getId());
   }

    return $response;
  });

  $this->post('/feedback', function ($request, $response, $args) use ($entityManager) {
    $client = new PF\Notifications\NotificationClient($entityManager);

    $flagged = $client->processFeedback();

    $response->setPinfinderMessage((count($flagged) > 0) ? 'Flagged ' . count($flagged) . ' tokens' : 'No tokens were flagged');
    $response->setPinfinderData([
      'tokens' => $flagged,
    ]);

    return $response;
  });

  $this->post('', function ($request, $response, $args) use ($entityManager, $serializer) {
    $json_notification_encoded = $request->getBody();

    $json_notification_decoded = json_decode($json_notification_encoded, true);

    $is_new_notification = empty($json_notification_decoded['id']);

    $notification_deserialization_context = DeserializationContext::create();
    $notification_deserialization_context->setGroups($is_new_notification ? array('create') : array('update'));

    $notification = $serializer->deserialize($json_notification_encoded, 'PF\Notification', 'json', $notification_deserialization_context);

    if (!empty($json_notification_decoded['user']['id'])) {
      $user = $entityManager->getRepository('\PF\User')->find($json_notification_decoded['user']['id']);

      $notification->setUser($user);
    }

    try {
      $entityManager->persist($notification);

      $entityManager->flush();

      $response = $response->withStatus($is_new_notification ? 201 : 200);

      $response->setPinfinderMessage(($is_new_notification ? 'Created Notification with ID ' : 'Updated Notification with ID ') . $notification->getId());
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $response = $response->withStatus(404);
    }

    return $response;
  });

  $this->delete('/{id}', function ($request, $response, $args) use ($entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($args['id']);

    if (empty($notification)) {
      $response = $response->withStatus(404);
    }
    else {
      $entityManager->remove($notification);

      $entityManager->flush();

      $response->setPinfinderMessage('Deleted Notification with ID ' . $notification->getId());
    }

    return $response;
  });

})->add(new \PF\Middleware\PinfinderAdminRouteMiddleware());
