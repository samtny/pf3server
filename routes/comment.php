<?php

use JMS\Serializer\DeserializationContext;

function comment_route_search($entityManager, $params) {
  $commentsIterator = $entityManager->getRepository('\PF\Comment')->getComments($params);

  $comments = [];

  foreach ($commentsIterator as $comment) {
    $comments[] = $comment;
  }

  return $comments;
}

$app->group('/comment', function () use ($entityManager, $serializer) {

  $this->get('/search', function ($request, $response, $args) use ($entityManager) {
    $comments = comment_route_search($entityManager, $request->getQueryParams());

    $response->setPinfinderData([
      'count' => count($comments),
      'comments' => $comments,
    ]);

    return $response;
  });

  $this->get('/{id}', function ($request, $response, $args) use ($entityManager) {
    $comment = $entityManager->getRepository('\PF\Comment')->find($args['id']);

    if (empty($comment)) {
      $response = $response->withStatus(404);
    }
    else {
      $response->setPinfinderData([
        'comment' => $comment,
      ]);
    }

    return $response;
  });

  $this->post('/{id}/approve', function ($request, $response, $args) use ($entityManager) {
    $comment = $entityManager->getRepository('\PF\Comment')->find($args['id']);

    if (empty($comment)) {
      $response = $response->withStatus(404);
    }

    $comment->approve();

    $entityManager->persist($comment);
    $entityManager->flush();

    $response->setPinfinderMessage('Approved Comment with ID ' . $comment->getId());

    return $response;
  });

  $this->post('', function ($request, $response, $args) use ($entityManager, $serializer) {
    $json_comment_encoded = $request->getBody();

    $json_comment_decoded = json_decode($json_comment_encoded, true);

    $is_new_comment = empty($json_comment_decoded['id']);

    $comment_deserialization_context = DeserializationContext::create();
    $comment_deserialization_context->setGroups($is_new_comment ? array('create') : array('update'));

    $comment = $serializer->deserialize($json_comment_encoded, 'PF\Comment', 'json', $comment_deserialization_context);

    try {
      $entityManager->persist($comment);

      $entityManager->flush();

      $response = $response->withStatus($is_new_comment ? 201 : 200);

      $response->setPinfinderMessage(($is_new_comment ? 'Created Comment with ID ' : 'Updated Comment with ID ') . $comment->getId());
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $response = $response->withStatus(404);
    }

    return $response;
  });

  $this->delete('/{id}', function ($request, $response, $args) use ($entityManager) {
    $comment = $entityManager->getRepository('\PF\Comment')->find($args['id']);

    if (empty($comment)) {
      $response = $response->withStatus(404);
    }
    else {
      $comment->touch();
      $comment->delete();

      $entityManager->persist($comment);

      $entityManager->flush();

      $response->setPinfinderMessage('Deleted Comment with ID ' . $comment->getId());
    }

    return $response;
  });

})->add(new \PF\Middleware\PinfinderAdminRouteMiddleware());
