<?php

use JMS\Serializer\DeserializationContext;

$app->group('/machine', function () use ($entityManager, $serializer) {

  $this->get('/{id}', function ($request, $response, $args) use ($entityManager) {
    $machine = $entityManager->getRepository('\PF\Machine')->find($args['id']);

    if (empty($machine)) {
      $response = $response->withStatus(404);
    }
    else {
      $response->setPinfinderData([
        'machine' => $machine,
      ]);
    }

    return $response;
  });

  $this->post('', function ($request, $response, $args) use ($entityManager, $serializer) {
    $json_machine_encoded = $request->getBody();

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

      $response = $response->withStatus($is_new_machine ? 201 : 200);

      $response->setPinfinderMessage(($is_new_machine ? 'Created Machine with ID ' : 'Updated Machine with ID ') . $machine->getId());
    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
      $response = $response->withStatus(404);
    }

    return $response;
  });

  $this->delete('/{id}', function ($request, $response, $args) use ($entityManager) {
    $machine = $entityManager->getRepository('\PF\Machine')->find($args['id']);

    if (empty($machine)) {
      $response = $response->withStatus(404);
    }
    else {
      $machine->touch();

      $machine->delete();

      $entityManager->persist($machine);

      $entityManager->flush();

      $response->setPinfinderMessage('Deleted Machine with ID ' . $machine->getId());
    }

    return $response;
  })->add(new \PF\Middleware\PinfinderAdminRouteMiddleware());

});
