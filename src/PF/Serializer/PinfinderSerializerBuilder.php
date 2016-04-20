<?php

namespace PF\Serializer;

use JMS\Serializer\SerializerBuilder;

class PinfinderSerializerBuilder {
  private $entityManager;
  private $cacheDir;
  private $debug;

  static function create() {
    return new static();
  }

  public function setEntityManager($entityManager) {
    $this->entityManager = $entityManager;

    return $this;
  }

  public function setDebug($debug) {
    $this->debug = $debug;

    return $this;
  }

  public function setCacheDir($cacheDir) {
    $this->cacheDir = $cacheDir;

    return $this;
  }

  public function build() {
    $objectConstructor = new PinfinderObjectConstructor($this->entityManager);

    $serializerBuilder = SerializerBuilder::create()
      ->setMetadataDirs(array('PF' => __DIR__ . '/yml'))
      ->setObjectConstructor($objectConstructor);

    $serializerBuilder->setDebug($this->debug);

    $serializerBuilder->setCacheDir($this->cacheDir);

    return $serializerBuilder->build();
  }
}
