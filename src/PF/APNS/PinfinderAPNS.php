<?php

namespace PF\APNS;

class PinfinderAPNS {
  private static $client_free;
  private static $client_pro;
  private static $feedback_client_free;
  private static $feedback_client_pro;
  private static $error_identifiers;

  const APNS_WRITE_RETRIES = 1;

  private static function createClient ($host, $port, $cert_path, $passphrase) {
    $streamContext = stream_context_create();

    stream_context_set_option($streamContext, 'ssl', 'local_cert', $cert_path);
    stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);

    $client = stream_socket_client('ssl://' . $host . ':' . $port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);

    return empty($error) ? $client : FALSE;
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

  /**
   * @param $notification
   * @param \Iterator $tokens
   * @return bool
   */
  public static function sendNotification($notification, $tokens) {
    self::$error_identifiers = array();

    $expiry = (new \DateTime('+24 hours'))->getTimestamp();

    $client = PinfinderAPNS::createClient('gateway.push.apple.com', 2195, __DIR__ . '/../../../ssl/PinfinderFreePushDist.includesprivatekey.pem', '');

    foreach ($tokens as $token) {
      if (!$token->isFlagged() && $token->getApp() === 'apnspro2') {
        if (!$client) {
          $client = self::createProClient();
        }

        $result = PinfinderAPNS::sendAlert($client, $token->getToken(), $token->getId(), $expiry, $notification->getMessage(), $notification->getQueryParams());
      }
    }

    $read = array($client);
    $write = NULL;
    $except = NULL;

    stream_select($read, $write, $except, 5);

    if (!empty($read)) {
      PinfinderAPNS::readBadTokens($client);
    }

    return empty(self::$error_identifiers);
  }

  public static function getErrorIdentifiers()
  {
    return self::$error_identifiers;
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

    $result = PinfinderAPNS::writeMessage($client, $apnsMessage);

    return ($result == FALSE || $client == FALSE) ? FALSE : $result;
  }

  public static function writeMessage($client, $apnsMessage, $nestLevel = 0) {
    $result = FALSE;

    try {
      $result = fwrite($client, $apnsMessage);
    } catch (\Exception $e) {
      $read = array($client);
      $write = array($client);
      $except = NULL;

      stream_select($read, $write, $except, 5);

      if (!empty($write) && $nestLevel < PinfinderAPNS::APNS_WRITE_RETRIES) {
        $result = PinfinderAPNS::writeMessage($client, $apnsMessage, $nestLevel + 1);
      } else if (!empty($read)) {
        PinfinderAPNS::readBadTokens($client);
      } else {
        fclose($client);
      }
    }

    return $result;
  }

  public static function readBadTokens($client) {
    while($client && !feof($client)) {
      $data = fread($client, 6);

      if(strlen($data) === 6) {
        $error = unpack("C1command/C1status/N1identifier", $data);

        self::$error_identifiers[] = $error['identifier'];
      }
    }
  }
}
