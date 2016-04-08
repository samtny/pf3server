<?php

namespace PF\Notifications;

class APNSService {
  public static function createClient ($host, $port, $cert_path, $passphrase) {
    $streamContext = stream_context_create();

    stream_context_set_option($streamContext, 'ssl', 'local_cert', $cert_path);
    stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);

    $client = stream_socket_client('ssl://' . $host . ':' . $port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);

    return empty($error) ? $client : FALSE;
  }

  public static function sendAlert($client, $deviceToken, $alert, $queryParams) {
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

    $apnsMessage = chr(0); // command
    $apnsMessage .= chr(0) . chr(32); //token length
    $apnsMessage .= pack('H*', $cleanDeviceToken); // token
    $apnsMessage .= chr(0) . chr(mb_strlen($payload)); // payload length
    $apnsMessage .= $payload;

    $result = fwrite($client, $apnsMessage);

    return ($result == FALSE || $client == FALSE) ? FALSE : $result;
  }
}
