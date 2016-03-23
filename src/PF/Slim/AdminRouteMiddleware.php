<?php

namespace PF\Slim;

use Slim\Slim;

class AdminRouteMiddleware
{
  public function call() {
    session_start();

    if (empty($_COOKIE['session'])) {
      $app = Slim::getInstance();

      $app->redirect('/login');
    }
  }
}
