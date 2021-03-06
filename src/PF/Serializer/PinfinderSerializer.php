<?php

namespace PF\Serializer;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class PinfinderSerializer extends Serializer {
  static function create($entityManager, $cache = true, $debug = false) {
    $pinfinder_object_constructor = new PinfinderObjectConstructor($entityManager);

    $serializer_builder = SerializerBuilder::create()
      ->setMetadataDirs(array('PF' => __DIR__ . '/yml'))
      ->setObjectConstructor($pinfinder_object_constructor);

    $serializer_builder->setDebug($debug);

    if ($cache) {
      $serializer_builder->setCacheDir(__DIR__ . '/../../../cache');
    }

    return $serializer_builder->build();
  }
}
