<?php

namespace PF\Slim;

use Slim\Views\Twig;

class PinfinderAppBuilder {
  private $runmode;
  private $entityManager;

  public static function create() {
    return new static();
  }

  public function setRunmode($runmode) {
    $this->runmode = $runmode;

    return $this;
  }

  public function setEntityManager($entityManager) {
    $this->entityManager = $entityManager;

    return $this;
  }

  public function build() {
    $app = new Slim(
      array(
        'mode' => $this->runmode,
        'view' => new Twig(),
      )
    );

    $app->configureMode('development', function () use ($app) {
      $app->config(array(
        'cookies.lifetime' => 'Never',
        'debug' => true,
      ));
    });

    $app->configureMode('production', function () use ($app) {
      $app->config(array(
        'cookies.lifetime' => '2 Hours',
        'debug' => false,
      ));
    });

    $app->view()->parserOptions = array(
      'autoescape' => false,
    );

    $app->notFound(function () use ($app) {
      $app->status(401);
      $app->render('404.html');
    });

    $app->setEntityManager($this->entityManager);

    $serializerBuilder = \PF\Serializer\PinfinderSerializerBuilder::create()
      ->setEntityManager($this->entityManager)
      ->setDebug($this->runmode === 'production');

    if ($this->runmode === 'production') {
      $serializerBuilder->setCachedir('/tmp');
    }

    $serializer = $serializerBuilder->build();

    $app->setSerializer($serializer);

    $app->add(new ResponseMiddleware($serializer));

    require 'routes/login.php';
    require 'routes/admin.php';
    require 'routes/venue.php';
    require 'routes/comment.php';
    require 'routes/game.php';
    Routes\Stats::register();
    require 'routes/geocode.php';
    require 'routes/notification.php';
    require 'routes/machine.php';
    require 'routes/legacy.php';

    return $app;
  }
}
