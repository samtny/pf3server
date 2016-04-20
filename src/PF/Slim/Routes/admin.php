<?php

$adminRouteMiddleware = new \PF\Slim\AdminRouteMiddleware();

$app->group('/admin', array($adminRouteMiddleware, 'call'), function () use ($app) {
  $app->any('', function () use ($app) {
    $app->render('admin.html');
  });
});
