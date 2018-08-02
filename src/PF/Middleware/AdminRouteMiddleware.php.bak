<?php

namespace PF\Middleware;

use Slim\Slim;

class AdminRouteMiddleware
{
  public function call() {
    session_start();

    if (empty($_COOKIE['session'])) {
      $app = Slim::getInstance();

      $app->status(401);
      $app->stop();
    }
  }
}
