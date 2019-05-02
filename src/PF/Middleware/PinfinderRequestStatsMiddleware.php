<?php

namespace PF\Middleware;

use PF\StatRecord;

class PinfinderRequestStatsMiddleware {

  /**
   * @param \Slim\Http\Request $request
   * @param $response
   * @param $next
   *
   * @return mixed $response
   */
  public function __invoke($request, $response, $next) {
    $entityManager = \Bootstrap::getEntityManager();
    $logger = \Bootstrap::getLogger();

    $stat = new StatRecord();
    $stat->setPath($request->getAttribute('route')->getName());
    $stat->setMethod($request->getMethod());
    $stat->setParams(!empty($request->getQueryParams()) ? json_encode($request->getQueryParams()) : NULL);

    try {
      $entityManager->persist($stat);
      $entityManager->flush($stat);
    } catch (\Exception $e) {
      $logger->error('Error persisting stat record', array('message' => $e->getMessage(), 'stat' => $stat));
    }

    return $next($request, $response);
  }

}
