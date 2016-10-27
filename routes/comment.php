<?php

use JMS\Serializer\DeserializationContext;

function comment_route_search($entityManager, $request) {
  $commentsIterator = $entityManager->getRepository('\PF\Comment')->getComments($request);

  $comments = [];

  foreach ($commentsIterator as $comment) {
    $comments[] = $comment;
  }

  return $comments;
}

$app->group('/comment', function () use ($app, $entityManager, $serializer, $adminRouteMiddleware) {
  $app->get('/search', function () use ($app, $entityManager) {
    $request = $app->request();

    $comments = comment_route_search($entityManager, $request);

    $app->responseData = array('count' => count($comments), 'comments' => $comments);
  });

  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $comment = $entityManager->getRepository('\PF\Comment')->find($id);

    if (empty($comment)) {
      $app->notFound();
    }

    $app->responseData = array('comment' => $comment);
  });

  $app->post('/:id/approve', array($adminRouteMiddleware, 'call'), function ($id) use ($app, $entityManager) {
    $comment = $entityManager->getRepository('\PF\Comment')->find($id);

    if (empty($comment)) {
      $app->notFound();
    }

    $comment->approve();

    $entityManager->persist($comment);
    $entityManager->flush();

    $app->responseMessage = 'Approved Comment with ID ' . $comment->getId();
  });

  $app->post('', function () use ($app, $entityManager, $serializer) {
    $json_comment_encoded = $app->request->getBody();

    $json_comment_decoded = json_decode($json_comment_encoded, true);

    $is_new_comment = empty($json_comment_decoded['id']);

    $comment_deserialization_context = DeserializationContext::create();
    $comment_deserialization_context->setGroups($is_new_comment ? array('create') : array('update'));

    $comment = $serializer->deserialize($json_comment_encoded, 'PF\Comment', 'json', $comment_deserialization_context);

    try {
      $entityManager->persist($comment);

      $entityManager->flush();

      $app->status($is_new_comment ? 201 : 200);

      $app->responseMessage = ($is_new_comment ? 'Created Comment with ID ' : 'Updated Comment with ID ') . $comment->getId();
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $app->notFound();
    }
  });

  $app->delete('/:id', array($adminRouteMiddleware, 'call'), function ($id) use ($app, $entityManager) {
    $comment = $entityManager->getRepository('\PF\Comment')->find($id);

    if (empty($comment)) {
      $app->notFound();
    }

    $comment->touch();

    $comment->delete();

    $entityManager->persist($comment);

    $entityManager->flush();

    $app->responseMessage = 'Deleted Comment with ID ' . $comment->getId();
  });
});
