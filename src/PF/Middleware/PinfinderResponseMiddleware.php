<?php

namespace PF\Middleware;

use \JMS\Serializer;
use \JMS\Serializer\SerializationContext;
use \Slim\Middleware;

class PinfinderResponseMiddleware {
  private $serializer;

  public function __construct($serializer) {
    $this->serializer = $serializer;
  }

  /**
   * Pinfinder response middleware
   *
   * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
   * @param  \PF\Slim\PinfinderResponse      $response PSR7 response
   * @param  callable                                 $next     Next middleware
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function __invoke($request, $response, $next) {
    $requestAuthenticated = !empty($_COOKIE['session']);

    $response = $next($request, $response);

    if (!empty($response->getPinfinderData()) || !empty($response->getPinfinderMessage())) {
      $output = array(
        'status' => $response->getStatusCode(),
      );

      if (!empty($response->getPinfinderData())) {
        $output['data'] = $response->getPinfinderData();
      }

      if (!empty($response->getPinfinderMessage())) {
        $output['message'] = $response->getPinfinderMessage();
      }

      $venue_serialization_context = SerializationContext::create();

      if ($requestAuthenticated) {
        $venue_serialization_context->setGroups(array('read', 'admin'));
      } else {
        $venue_serialization_context->setGroups(array('read'));
      }

      $output = $this->serializer->serialize($output, 'json', $venue_serialization_context);

      header('Content-Type: application/json;charset=UTF-8');
      header('PF-Memory-Get-Peak-Usage: ' . memory_get_peak_usage());

      echo $output;
    }

    return $response;
  }
}
