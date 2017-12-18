<?php

use JMS\Serializer\DeserializationContext;

$app->group('/machine', function () use ($app, $entityManager, $serializer, $adminRouteMiddleware) {
  $app->get('/:id', function ($id) use ($app, $entityManager) {
    $machine = $entityManager->getRepository('\PF\Machine')->find($id);

    if (empty($machine)) {
      $app->notFound();
    }

    $app->responseData = array('machine' => $machine);
  });

  $app->post('', function () use ($app, $entityManager, $serializer) {
    $json_machine_encoded = $app->request->getBody();

    $json_machine_decoded = json_decode($json_machine_encoded, true);

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

    if (!$is_new_machine) {
      $venue = $machine->getVenue();

      $venue->touch();

      $entityManager->persist($venue);
    }

    try {
      $entityManager->persist($machine);

      $entityManager->flush();

      $app->status($is_new_machine ? 201 : 200);

      $app->responseMessage = ($is_new_machine ? 'Created Machine with ID ' : 'Updated Machine with ID ') . $machine->getId();
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $app->notFound();
    }
  });

  $app->delete('/:id', array($adminRouteMiddleware, 'call'), function ($id) use ($app, $entityManager) {
    $machine = $entityManager->getRepository('\PF\Machine')->find($id);

    if (empty($machine)) {
      $app->notFound();
    }

    $machine->touch();

    $machine->delete();

    $entityManager->persist($machine);

    $entityManager->flush();

    $app->responseMessage = 'Deleted Machine with ID ' . $machine->getId();
  });
});
