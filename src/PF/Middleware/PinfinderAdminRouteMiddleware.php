<?php

namespace PF\Middleware;

class PinfinderAdminRouteMiddleware
{
  public function __invoke($request, $response, $next) {
    session_start();

    if (empty($_COOKIE['session'])) {
      return $response->withStatus(401);
    } else {
      return $next($request, $response);
    }
  }
}
