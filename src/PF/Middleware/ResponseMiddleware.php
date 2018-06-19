<?php

namespace PF\Middleware;

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
    $app = $this->getApplication();

    $app->requestAuthenticated = !empty($_COOKIE['session']);

    $this->next->call();

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

      if ($app->requestAuthenticated) {
        $venue_serialization_context->setGroups(array('read', 'admin'));
      } else {
        $venue_serialization_context->setGroups(array('read'));
      }

      $response = $this->serializer->serialize($response, 'json', $venue_serialization_context);

      header('Content-Type: application/json;charset=UTF-8');
      header('PF-Memory-Get-Peak-Usage: ' . memory_get_peak_usage());

      echo $response;
    }
  }
}
