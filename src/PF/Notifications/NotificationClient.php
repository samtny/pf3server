<?php

namespace PF\Notifications;

use FastAPNS;

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
  public function sendNotification($notification) {
    $num_tokens = 0;
    $num_bad_tokens = 0;

    if ($notification->getGlobal() === true) {
      $tokensIterator = $this->entityManager->getRepository('\PF\Token')->getValidTokens();

      PinfinderAPNS::sendNotification($notification, $tokensIterator);
    } else {
      $user = $notification->getUser();

      if (!empty($user)) {
        $tokens = $user->getTokens();

        $num_tokens += count($tokens);

        if (!empty($tokens)) {
          $payload = array(
            'aps' => array(
              'alert' => $notification->getMessage(),
            ),
          );

          if (!empty($notification->getQueryParams())) {
            $payload['queryparams'] = $notification->getQueryParams();
          }

          $tokens_free = array();
          $tokens_pro = array();

          foreach ($tokens as $token) {
            if (strpos($token->getApp(), 'apnsfree') === 0) {
              $tokens_free[] = $token->getToken();
            } else {
              $tokens_pro[] = $token->getToken();
            }
          }

          if (!empty($tokens_free)) {
            $client = FastAPNS\ClientBuilder::create()
              ->setLocalCert(__DIR__ . '/../../../ssl/PinfinderFreePushDist.includesprivatekey.pem')
              ->setPassphrase('')
              ->build();

            $client->send($payload, $tokens_free, (new \DateTime('+24 hours'))->getTimestamp());

            if (!empty($client->getBadTokens())) {
              foreach ($client->getBadTokens() as $tokenString) {
                $token = $this->entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString, 'app' => 'apnsfree'));

                if (empty($token)) {
                  $token = $this->entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString, 'app' => 'apnsfree2'));
                }

                if (!empty($token)) {
                  $token->flag();

                  $this->entityManager->persist($token);

                  $num_bad_tokens += 1;
                }
              }
            }
          }

          if (!empty($tokens_pro)) {
            $client = FastAPNS\ClientBuilder::create()
              ->setLocalCert(__DIR__ . '/../../../ssl/PinfinderProPushDist.includesprivatekey.pem')
              ->setPassphrase('')
              ->build();

            $client->send($payload, $tokens_pro, (new \DateTime('+24 hours'))->getTimestamp());

            if (!empty($client->getBadTokens())) {
              foreach ($client->getBadTokens() as $tokenString) {
                $token = $this->entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString, 'app' => 'apnspro'));

                if (empty($token)) {
                  $token = $this->entityManager->getRepository('\PF\Token')->findOneBy(array('token' => $tokenString, 'app' => 'apnspro2'));
                }

                if (!empty($token)) {
                  $token->flag();

                  $this->entityManager->persist($token);

                  $num_bad_tokens += 1;
                }
              }
            }
          }
        }
      }
    }

    return array(
      'num_tokens' => $num_tokens,
      'num_bad_tokens' => $num_bad_tokens,
    );
  }
}
