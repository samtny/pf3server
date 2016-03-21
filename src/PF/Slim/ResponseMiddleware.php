<?php

namespace PF\Slim;

use \JMS\Serializer;
use \JMS\Serializer\SerializationContext;
use \Slim\Middleware;

class ResponseMiddleware extends Middleware
{
  private $serializer;

  public function __construct($serializer) {
    $this->serializer = $serializer;
  }

  public function call() {
    $this->next->call();

    $app = $this->getApplication();

    if (!empty($app->responseData) || !empty($app->responseMessage)) {
      $response = array(
        'status' => $app->response->getStatus(),
      );

      if (!empty($app->responseData)) {
        $response['data'] = $app->responseData;
      }

      if (!empty($app->responseMessage)) {
        $response['message'] = $app->responseMessage;
      }

      $venue_serialization_context = SerializationContext::create();

      $venue_serialization_context->setGroups(array('read'));

      $response = $this->serializer->serialize($response, 'json', $venue_serialization_context);

      header('Content-Type: application/json');
      header('PF-Memory-Get-Peak-Usage: ' . memory_get_peak_usage());

      echo $response;
    }
  }
}
