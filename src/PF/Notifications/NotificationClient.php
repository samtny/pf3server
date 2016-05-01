<?php

namespace PF\Notifications;

use FastAPNS;
use PF\Notification;

class NotificationClient {
  /**
   * @var \Doctrine\ORM\EntityManager
   */
  private $entityManager;

  public function __construct($entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * @param \PF\Notification $notification
   * @return array
   */
  public function sendNotification(Notification $notification) {
    $num_tokens = 0;
    $num_bad_tokens = 0;

    if ($notification->getGlobal() === true) {
      $tokensIterator = $this->entityManager->getRepository('\PF\Token')->getValidTokens();

      PinfinderAPNS::sendNotification($notification, $tokensIterator);
    } else {
      $user = $notification->getUser();

      if (!empty($user)) {
        $apps = array('apnsfree', 'apnspro');

        $payload = array(
          'aps' => array(
            'alert' => $notification->getMessage(),
          ),
        );

        if (!empty($notification->getQueryParams())) {
          $payload['queryparams'] = $notification->getQueryParams();
        }

        foreach ($apps as $app) {
          $tokens = $user->getTokenStrings($app);

          if (!empty($tokens)) {
            $this->sendPayload($payload, $app, $tokens, (new \DateTime('+24 hours'))->getTimestamp());
          }
        }
      }
    }

    return array(
      'num_tokens' => $num_tokens,
      'num_bad_tokens' => $num_bad_tokens,
    );
  }

  public function sendPayload($payload, $app, $tokens, $expiry) {
    $client = FastAPNS\ClientBuilder::create()
      ->setLocalCert(\Bootstrap::getConfig()['pf3server_ssl'] . '/Pinfinder' . ($app === 'apnsfree' ? 'Free' : 'Pro') . 'PushDist.includesprivatekey.pem')
      ->setPassphrase('')
      ->build();

    $client->send($payload, $tokens, $expiry);

    if (!empty($client->getBadTokens())) {
      foreach ($client->getBadTokens() as $tokenString) {
        $token = $this->entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString, 'app' => $app));

        if (!empty($token)) {
          $token->flag();

          $this->entityManager->persist($token);
        }
      }
    }
  }

  public function processFeedback() {
    $flagged = array();

    $client = FastAPNS\ClientBuilder::create()
      ->setHost('feedback.push.apple.com')
      ->setPort(2196)
      ->setLocalCert(__DIR__ . '/../../../ssl/PinfinderFreePushDist.includesprivatekey.pem')
      ->setPassphrase('')
      ->build();

    $tokens = $client->getFeedbackTokens();

    foreach ($tokens as $tokenString) {
      $flagged[] = $tokenString;

      $token = $this->entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString, 'app' => 'apnsfree'));

      if (!empty($token)) {
        $token->flag();

        $this->entityManager->persist($token);
      }
    }

    $client = FastAPNS\ClientBuilder::create()
      ->setHost('feedback.push.apple.com')
      ->setPort(2196)
      ->setLocalCert(__DIR__ . '/../../../ssl/PinfinderProPushDist.includesprivatekey.pem')
      ->setPassphrase('')
      ->build();

    $tokens = $client->getFeedbackTokens();

    foreach ($tokens as $tokenString) {
      $flagged[] = $tokenString;

      $token = $this->entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString, 'app' => 'apnspro'));

      if (!empty($token)) {
        $token->flag();

        $this->entityManager->persist($token);
      }
    }

    $this->entityManager->flush();

    return $flagged;
  }
}
