<?php

use JMS\Serializer\DeserializationContext;

$app->group('/notification', array($adminRouteMiddleware, 'call'), function () use ($app, $entityManager, $serializer) {
  $app->get('/search', function () use ($app, $entityManager) {
    $notificationsIterator = $entityManager->getRepository('\PF\Notification')->getNotifications($app->request());

    $notifications = [];

    foreach ($notificationsIterator as $notification) {
      $notifications[] = $notification;
    }

    $app->responseData = array('count' => count($notifications), 'notifications' => $notifications);
  });

  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($id);

    if (empty($notification)) {
      $app->notFound();
    }

    $app->responseData = array('notification' => $notification);
  });

  $app->post('/all/send', function () use ($app, $entityManager) {
    $notificationsIterator = $entityManager->getRepository('\PF\Notification')->getPendingNotifications();

    $count = 0;

    $client = new PF\Notifications\NotificationClient($entityManager);

    $data = array(
      'num_tokens' => 0,
      'num_bad_tokens' => 0,
    );

    foreach ($notificationsIterator as $notification) {
      $count += 1;

      $result = $client->sendNotification($notification);

      $data['num_tokens'] += $result['num_tokens'];
      $data['num_bad_tokens'] += $result['num_bad_tokens'];

      $notification->archive();

      $entityManager->persist($notification);
    }

    if ($data['num_tokens'] > 0) {
      $app->responseMessage = 'Sent ' . $count . ' Notifications';

      if (!empty($data['num_bad_tokens'])) {
        $app->responseMessage .= ' (Flagged ' . $data['num_bad_tokens'] . ' tokens)';
      }
    }

    $entityManager->flush();
  });

  $app->post('/:id/send', function ($id) use ($app, $entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($id);

    if (empty($notification)) {
      $app->notFound();
    }

    $client = new PF\Notifications\NotificationClient($entityManager);

    $result = $client->sendNotification($notification);

    if ($result['num_tokens'] > 0) {
      $app->responseMessage = 'Sent Notification with ID ' . $notification->getId();

      if (!empty($result['num_bad_tokens'])) {
        $app->responseMessage .= ' (Tokens Flagged: ' . $result['num_bad_tokens'] . ')';
      }
    }

    $notification->archive();

    $entityManager->persist($notification);

    $entityManager->flush();
  });

  $app->post('/feedback', function () use ($app, $entityManager, $serializer) {
    $client = new PF\Notifications\NotificationClient($entityManager);

    $flagged = $client->processFeedback();

    $app->responseMessage = (count($flagged) > 0) ? 'Flagged ' . count($flagged) . ' tokens' : 'No tokens were flagged';
    $app->responseData = array('tokens' => $flagged);
  });

  $app->post('', function () use ($app, $entityManager, $serializer) {
    $json_notification_encoded = $app->request->getBody();

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

      $app->status($is_new_notification ? 201 : 200);

      $app->responseMessage = ($is_new_notification ? 'Created Notification with ID ' : 'Updated Notification with ID ') . $notification->getId();
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $app->notFound();
    }
  });

  $app->delete('/:id', function ($id) use ($app, $entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($id);

    if (empty($notification)) {
      $app->notFound();
    }

    $entityManager->remove($notification);

    $entityManager->flush();

    $app->responseMessage = 'Deleted Notification with ID ' . $notification->getId();
  });
});
