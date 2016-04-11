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

    $count = 0;

    foreach ($notificationsIterator as $notification) {
      $count += 1;

      if ($notification->getGlobal() === true) {
        $tokensIterator = $entityManager->getRepository('\PF\Token')->getValidTokens();

        APNSService::sendNotification($notification, $tokensIterator);
      } else {
        $user = $notification->getUser();

        if (!empty($user)) {
          $tokens = $user->getTokens();

          if (!empty($tokens)) {
            APNSService::sendNotification($notification, $tokens);
          }
        }
      }

      $notification->archive();

      $entityManager->persist($notification);
    }

    $entityManager->flush();

    $app->responseMessage = $count > 0 ? 'Sent ' . $count . ' notification(s)' : 'Nothing to send';
  });

  $app->post('/:id/send', function ($id) use ($app, $entityManager) {
    $notification = $entityManager->getRepository('\PF\Notification')->find($id);

    if (empty($notification)) {
      $app->notFound();
    }

    $user = $notification->getUser();

    if (!empty($user)) {
      $tokens = $user->getTokens();

      if (!empty($tokens)) {
        APNSService::sendNotification($notification, $tokens);
      }
    }

    $notification->archive();

    $entityManager->persist($notification);

    $entityManager->flush();

    $app->responseMessage = 'Sent Notification with ID ' . $notification->getId();
  });

  $app->post('/feedback', function () use ($app, $entityManager, $serializer) {
    $feedback_tokens = APNSService::getFeedbackTokens();

    foreach ($feedback_tokens as $feedback_token) {
      if (!empty($feedback_token['devtoken'])) {
        $token = $entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $feedback_token['devtoken']));

        if (!empty($token)) {
          $token->flag();

          $entityManager->persist($token);
        }
      }
    }

    $entityManager->flush();

    $app->responseMessage = !empty($feedback_tokens) ? 'Flagged ' . count($feedback_tokens) . ' tokens' : 'No tokens were flagged';
    $app->responseData = array('feedback_tokens' => $feedback_tokens);
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
