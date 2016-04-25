<?php

namespace PF\Slim;

use Slim\Middleware;

class XHProfMiddleware extends Middleware
{
  public function call() {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

    $this->next->call();

    $xhprof_data = xhprof_disable();

    $XHPROF_ROOT = "/usr/share/php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

    $xhprof_runs = new \XHProfRuns_Default();
    $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
  }
}
