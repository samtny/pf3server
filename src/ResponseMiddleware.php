<?php

namespace PF;

use \Slim\Middleware;
use \JMS\Serializer\SerializerBuilder;

class ResponseMiddleware extends Middleware
{
  public function call() {
    $this->next->call();

    $app = $this->getApplication();

    if (!empty($app->responseData)) {

      $serializer = SerializerBuilder::create()->build();

      $res = $app->response();
      $res['Content-Type'] = 'application/json';

      $response = array(
        'status' => $app->response->getStatus(),
        'meta' => array(
          'memory_get_peak_usage' => memory_get_peak_usage(),
        ),
      );

      if (!empty($app->responseData)) {
        $response['data'] = $app->responseData;
      }

      echo $serializer->serialize($response, 'json');
    }
  }
}
