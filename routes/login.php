<?php

use Dflydev\FigCookies\FigResponseCookies;

$app->any('/login', function ($request, $response, $args) use ($entityManager) {
  $username = $request->getParsedBodyParam('username');
  $password = $request->getParsedBodyParam('password');

  $username_addslashes = addslashes($username);
  $password_md5 = md5($password);

  $user = $entityManager->getRepository('\PF\User')->findOneBy(array('username' => $username_addslashes, 'password' => $password_md5));

  if (empty($user)) {
    $response = $response->withStatus(401);
  } else {
    session_start();

    $_SESSION['username'] = $username_addslashes;
    $_SESSION['password'] = $password_md5;

    $session = $entityManager->getRepository('\PF\Session')->findOneBy(array('user' => $user));

    if (empty($session)) {
      $session = new \PF\Session();
      $session->setUser($user);

      $entityManager->persist($session);
      $entityManager->flush();
    }

    $response = FigResponseCookies::set($response, \Dflydev\FigCookies\SetCookie::create('session')
      ->withValue($session->getId())
      ->withMaxAge('366 days')
    );

    $response = $response->withStatus(200);
  }

  return $response;
});
