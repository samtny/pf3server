<?php

namespace PF\Slim;

use Slim\Slim;

class AdminRouteMiddleware
{
  public function call() {
    $app = Slim::getInstance();
    $app->halt(401);
  }
}
