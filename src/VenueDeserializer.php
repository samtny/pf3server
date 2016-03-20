<?php

namespace PF;

use \JMS\Serializer\DeserializationContext;

class VenueDeserializer
{
  private $serializer;
  private $entityManager;

  public function getSerializer() {
    return $this->serializer;
  }

  public function setSerializer($serializer) {
    $this->serializer = $serializer;
  }

  public function getEntityManager() {
    return $this->entityManager;
  }

  public function setEntityManager($entityManager) {
    $this->entityManager = $entityManager;
  }

  public function deserialize($json_venue_encoded) {
    $json_venue_decoded = json_decode($json_venue_encoded, true);

    $is_new_venue = empty($json_venue_decoded['id']);

    $venue_deserialization_context = DeserializationContext::create();

    if ($is_new_venue) {
      $venue_deserialization_context->setGroups(array('create'));
      $venue_deserialization_context->setAttribute('target', new Venue());
    } else {
      $venue_deserialization_context->setGroups(array('update'));
      $venue_deserialization_context->setAttribute('target', $this->getEntityManager()->getRepository('\PF\Venue')->find($json_venue_decoded['id']));
    }

    $venue = $this->getSerializer()->deserialize($json_venue_encoded, 'PF\Venue', 'json', $venue_deserialization_context);

    foreach ($json_venue_decoded['machines'] as $json_machine_decoded) {
      $json_machine_encoded = json_encode($json_machine_decoded);

      $is_new_machine = empty($json_machine_decoded['id']);

      $machine_deserialization_context = DeserializationContext::create();

      if ($is_new_machine) {
        $machine_deserialization_context->setGroups(array('create'));
        $machine_deserialization_context->setAttribute('target', new Machine());
      } else {
        $machine_deserialization_context->setGroups(array('update'));
        $machine_deserialization_context->setAttribute('target', $this->getEntityManager()->getRepository('\PF\Machine')->find($json_machine_decoded['id']));
      }

      $machine = $this->getSerializer()->deserialize($json_machine_encoded, 'PF\Machine', 'json', $machine_deserialization_context);

      $machine->setGame($this->getEntityManager()->getRepository('\PF\Game')->find($machine->getIpdb()));

      $venue->addMachine($machine);
    }

    foreach ($json_venue_decoded['comments'] as $json_comment_decoded) {
      $json_comment_encoded = json_encode($json_comment_decoded);

      $is_new_comment = empty($json_comment_decoded['id']);

      $comment_deserialization_context = DeserializationContext::create();

      if ($is_new_comment) {
        $comment_deserialization_context->setGroups(array('create'));
        $comment_deserialization_context->setAttribute('target', new Comment());
      } else {
        $comment_deserialization_context->setGroups(array('update'));
        $comment_deserialization_context->setAttribute('target', $this->getEntityManager()->getRepository('\PF\Comment')->find($json_comment_decoded['id']));
      }

      $comment = $this->getSerializer()->deserialize($json_comment_encoded, 'PF\Comment', 'json', $comment_deserialization_context);

      $venue->addComment($comment);
    }

    return $venue;
  }
}
