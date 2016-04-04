<?php

$app->any('/login', function () use ($app, $entityManager) {
  $username = $app->request->post('username');
  $password = $app->request->post('password');

  $username_addslashes = addslashes($username);
  $password_md5 = md5($password);

  $user = $entityManager->getRepository('\PF\User')->findOneBy(array('username' => $username_addslashes, 'password' => $password_md5));

  if (empty($user)) {
    $app->render('login.html');
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

    $app->setCookie('session', $session->getId(), '365 days');

    $app->redirect('/admin');
  }
});
