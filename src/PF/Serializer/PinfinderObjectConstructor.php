<?php

namespace PF\Serializer;

use JMS\Serializer\VisitorInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Construction\ObjectConstructorInterface;

class PinfinderObjectConstructor implements ObjectConstructorInterface {
  /**
   * @type \Doctrine\ORM\EntityManager
   */
  private $entityManager;

  /**
   * PinfinderObjectConstructor constructor.
   * @param \Doctrine\ORM\EntityManager $entityManager
   */
  public function __construct($entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * @inheritdoc
   */
  public function construct(VisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context)
  {
    if (!empty($data['id'])) {
      return $this->entityManager->find($metadata->name, $data['id']);
    } else {
      return new $metadata->name();
    }
  }
}
