<?php

namespace PF\Slim;

class Slim extends \Slim\Slim {
  /**
   * @var array[\PF\Slim\Slim]
   */
  protected static $apps = array();

  /**
   * @var \Doctrine\ORM\EntityManager $entityManager
   */
  private $entityManager;
  private $serializer;
  private $responseData;
  private $responseMessage;

  /**
   * Get application instance by name
   * @param  string    $name The name of the Slim application
   * @return \PF\Slim|Slim
   */
  public static function getInstance($name = 'default')
  {
    return isset(static::$apps[$name]) ? static::$apps[$name] : null;
  }

  public function setEntityManager($entityManager) {
    $this->entityManager = $entityManager;
  }

  public function getEntityManager() {
    return $this->entityManager;
  }

  public function setSerializer($serializer) {
    $this->serializer = $serializer;
  }

  public function getSerializer() {
    return $this->serializer;
  }

  public function setResponseData($responseData) {
    $this->responseData = $responseData;
  }

  public function getResponseData() {
    return $this->responseData;
  }

  public function setResponseMessage($responseMessage) {
    $this->responseMessage = $responseMessage;
  }

  public function getResponseMessage() {
    return $this->responseMessage;
  }
}
