<?php

namespace PF\Notifications;

class PinfinderAPNS {
  private static $client_free;
  private static $client_pro;
  private static $feedback_client_free;
  private static $feedback_client_pro;
  private static $errors;

  private static function createClient ($host, $port, $cert_path, $passphrase) {
    $streamContext = stream_context_create();

    stream_context_set_option($streamContext, 'ssl', 'local_cert', $cert_path);
    stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);

    $client = stream_socket_client('ssl://' . $host . ':' . $port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);

    return empty($error) ? $client : FALSE;
  }

  public static function createFreeClient() {
    if (!self::$client_free) {
      self::$client_free = PinfinderAPNS::createClient('gateway.push.apple.com', 2195, __DIR__ . '/../../../ssl/PinfinderFreePushDist.includesprivatekey.pem', '');
    }

    return self::$client_free;
  }

  public static function createProClient() {
    if (!self::$client_pro) {
      self::$client_pro = PinfinderAPNS::createClient('gateway.push.apple.com', 2195, __DIR__ . '/../../../ssl/PinfinderProPushDist.includesprivatekey.pem', '');
    }

    return self::$client_pro;
  }

  public static function createFreeFeedbackClient() {
    if (!self::$feedback_client_free) {
      self::$feedback_client_free = PinfinderAPNS::createClient('feedback.push.apple.com', 2196, __DIR__ . '/../../../ssl/PinfinderFreePushDist.includesprivatekey.pem', '');
    }

    return self::$feedback_client_free;
  }

  public static function createProFeedbackClient() {
    if (!self::$feedback_client_pro) {
      self::$feedback_client_pro = PinfinderAPNS::createClient('feedback.push.apple.com', 2196, __DIR__ . '/../../../ssl/PinfinderProPushDist.includesprivatekey.pem', '');
    }

    return self::$feedback_client_pro;
  }

  public static function getFeedbackTokens() {
    $feedback_tokens = array();

    $client = PinfinderAPNS::createFreeFeedbackClient();

    while(!feof($client)) {
      $data = fread($client, 38);

      if(strlen($data)) {
        $feedback_tokens[] = unpack("N1timestamp/n1length/H*devtoken", $data);
      }
    }
    fclose($client);

    $client = PinfinderAPNS::createProFeedbackClient();

    while(!feof($client)) {
      $data = fread($client, 38);

      if(strlen($data)) {
        $feedback_tokens[] = unpack("N1timestamp/n1length/H*devtoken", $data);
      }
    }
    fclose($client);

    return $feedback_tokens;
  }

  public static function sendNotification($notification, $tokens) {
    self::$errors = array();

    $expiry = (new \DateTime('+24 hours'))->getTimestamp();

    foreach ($tokens as $token) {
      if (!$token->isFlagged()) {
        if ($token->getToken() == 'apnsfree' || $token->getToken() == 'apnspro') {
          $client = $token->getApp() === 'apnsfree' ? self::createFreeClient() : self::createProClient();

          $result = PinfinderAPNS::sendAlert($client, $token->getToken(), $token->getId(), $expiry, $notification->getMessage(), $notification->getQueryParams());
        }
      }
    }

    return empty(self::$errors);
  }

  public static function getErrors() {
    return self::$errors;
  }

  public static function sendAlert($client, $deviceToken, $identifier, $expiry, $alert, $queryParams) {
    $cleanDeviceToken = preg_replace('/\s|<|>/', '', $deviceToken);

    $payload = array(
      'aps' => array(
        'alert' => $alert,
      ),
    );

    if (!empty($queryParams)) {
      $payload['queryparams'] = $queryParams;
    }

    $payload = json_encode($payload);

    $apnsMessage = chr(1); // command (enhanced notification format)
    $apnsMessage .= pack('N', $identifier); // 4-byte identifier
    $apnsMessage .= pack('N', $expiry); // 4-byte expiry
    $apnsMessage .= chr(0) . chr(32); //token length
    $apnsMessage .= pack('H*', $cleanDeviceToken); // token
    $apnsMessage .= chr(0) . chr(mb_strlen($payload)); // payload length
    $apnsMessage .= $payload;

    try {
      $result = fwrite($client, $apnsMessage);
    } catch (\Exception $e) {
      try {
        // try again
        $result = fwrite($client, $apnsMessage);
      } catch (\Exception $e) {
        while(!feof($client)) {
          $data = fread($client, 6);

          if (!strlen($data)) {
            // connection closed
            fclose($client);

            var_dump('connection closed');exit;
          } else if(strlen($data) === 6) {
            $error = unpack("C1command/C1status/N1identifier", $data);

            switch ($error['status']) {
              case 8:
                // bad token
                var_dump($error);exit;

                break;
              case 10:
                // connection 'closed'
                fclose($client);

                var_dump($error);exit;

                break;
              default:
                // other error (token not necessarily bad)
                var_dump($error);exit;

                break;
            }
          }
        }
      }
    }

    return ($result == FALSE || $client == FALSE) ? FALSE : $result;
  }
}
