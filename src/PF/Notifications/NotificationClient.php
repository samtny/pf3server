<?php

namespace PF\Notifications;

use Doctrine\ORM\Query;
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

  public function getValidApps() {
    return array(
      'apnsfree',
      'apnspro'
    );
  }

  /**
   * @param \PF\Notification $notification
   * @return array
   */
  public function sendNotification(Notification $notification) {
    if ($notification->getGlobal() === true) {
      $payload = array(
        'aps' => array(
          'alert' => $notification->getMessage(),
        ),
      );

      if (!empty($notification->getQueryParams())) {
        $payload['queryparams'] = $notification->getQueryParams();
      }

      foreach ($this->getValidApps() as $app) {
        $tokens = $this->entityManager->getRepository('\PF\Token')->getValidTokens($app, Query::HYDRATE_ARRAY);

        if (!empty($tokens)) {
          $token_strings = array();

          foreach ($tokens as $token) {
            $token_strings[] = $token['token'];
          }

          $this->sendPayload($payload, $app, $token_strings, (new \DateTime('+24 hours'))->getTimestamp());
        }
      }
    } else {
      $user = $notification->getUser();

      if (!empty($user)) {
       $payload = array(
          'aps' => array(
            'alert' => $notification->getMessage(),
          ),
        );

        if (!empty($notification->getQueryParams())) {
          $payload['queryparams'] = $notification->getQueryParams();
        }

        foreach ($this->getValidApps() as $app) {
          $tokens = $user->getTokenStrings($app);

          if (!empty($tokens)) {
            $this->sendPayload($payload, $app, $tokens, (new \DateTime('+24 hours'))->getTimestamp());
          }
        }
      }
    }
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

      $this->entityManager->flush();
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
