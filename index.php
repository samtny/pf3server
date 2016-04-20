<?php

require_once 'bootstrap.php';

$runmode = Bootstrap::getRunmode();
$entityManager = Bootstrap::getEntityManager();

$appBuilder = \PF\Slim\PinfinderAppBuilder::create()
  ->setRunmode($runmode)
  ->setEntityManager($entityManager);

$app = $appBuilder->build();

$app->run();
