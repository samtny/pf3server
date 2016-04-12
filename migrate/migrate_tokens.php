<?php

require __DIR__ .  '/../bootstrap.php';

$contents = file_get_contents(__DIR__ . "/tokens.txt");

$separator = "\r\n";
$line = strtok($contents, $separator);

$new = 0;

while ($line !== false) {
  $parts = explode(',', $line);

  if (count($parts) == 2) {
    $app = $parts[1];
    $tokenString = $parts[0];

    if ($app == 'apnsfree' || $app == 'apnspro') {
      $token = $entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString, 'app' => $app));

      if (empty($token)) {
        $user = new PF\User();

        $token = new PF\Token();
        $token->setApp($app);
        $token->setToken($tokenString);

        $user->addToken($token);

        $entityManager->persist($user);

        $new += 1;
      }
    }
  }

  if ($new % 100 == 0) {
    $entityManager->flush();
  }

  $line = strtok( $separator );
}

$entityManager->flush();

echo "Generated " . $new . ' new tokens' . "\n";
