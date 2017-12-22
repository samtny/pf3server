<?php

namespace PF\Middleware;

use PF\StatRecord;
use Slim\Middleware;
use Slim\Slim;

class RequestStatsMiddleware extends Middleware {

  public function call() {
    $entityManager = \Bootstrap::getEntityManager();
    $logger = \Bootstrap::getLogger();

    $app = Slim::getInstance();

    $request = $app->request();

    $stat = new StatRecord();
    $stat->setPath($request->getPath());
    $stat->setMethod($request->getMethod());
    $stat->setParams(!empty($request->params()) ? json_encode($request->params()) : NULL);

    try {
      $entityManager->persist($stat);
      $entityManager->flush($stat);
    } catch (\Exception $e) {
      $logger->error('Error persisting stat record', array('message' => $e->getMessage(), 'stat' => $stat));
    }

  }

}
