<?php

use JMS\Serializer\DeserializationContext;
use PF\Notifications\APNSService;

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

    foreach ($notificationsIterator as $notification) {
      if ($notification->getGlobal() === true) {
        $tokensIterator = $entityManager->getRepository('\PF\Token')->getValidTokens();

        APNSService::sendGlobalNotification($notification, $tokensIterator);
      } else {
        APNSService::sendUserNotification($notification);
      }

      foreach (APNSService::getErrors() as $tokenString) {
        $token = $entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString));

        $token->flag();

        $entityManager->persist($token);
      }

      $notification->archive();

      $entityManager->persist($notification);
    }

    $entityManager->flush();

    $app->responseMessage = 'Sent ' . count($notifications) . ' notification(s)';
  });

  $app->post('/:id/send', function ($id) use ($app, $entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($id);

    if (empty($notification)) {
      $app->notFound();
    }

    APNSService::sendUserNotification($notification);

    foreach (APNSService::getErrors() as $tokenString) {
      $token = $entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString));

      $token->flag();

      $entityManager->persist($token);
    }

    $notification->archive();

    $entityManager->persist($notification);

    $entityManager->flush();

    $app->responseMessage = 'Sent Notification with ID ' . $notification->getId();
  });

  $app->post('/:id/approve', function ($id) use ($app, $entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($id);

    if (empty($notification)) {
      $app->notFound();
    }

    $notification->approve();

    $entityManager->persist($notification);
    $entityManager->flush();

    $app->responseMessage = 'Approved Notification with ID ' . $notification->getId();
  });

  $app->post('', function () use ($app, $entityManager, $serializer) {
    $json_notification_encoded = $app->request->getBody();

    $json_notification_decoded = json_decode($json_notification_encoded, true);

    $is_new_notification = empty($json_notification_decoded['id']);

    $notification_deserialization_context = DeserializationContext::create();
    $notification_deserialization_context->setGroups($is_new_notification ? array('create') : array('update'));

    $notification = $serializer->deserialize($json_notification_encoded, 'PF\Notification', 'json', $notification_deserialization_context);

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
