<?php

namespace PF\Middleware;

class PinfinderXHProfMiddleware
{

  public function __invoke($request, $response, $next) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

    $next($request, $response);

    $xhprof_data = xhprof_disable();

    $XHPROF_ROOT = "/usr/share/php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

    $xhprof_runs = new \XHProfRuns_Default();
    $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
  }

}
