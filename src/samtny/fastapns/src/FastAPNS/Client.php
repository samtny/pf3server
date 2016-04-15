<?php

namespace FastAPNS;

class Client {
  const FASTAPNS_GATEWAY_HOST = 'gateway.push.apple.com';
  const FASTAPNS_GATEWAY_PORT = 2195;
  const FASTAPNS_BATCH_SIZE = 1700;

  /**
   * @var ClientStreamSocket
   */
  private $client_stream_socket;

  private $tokensSent;
  private $badTokens;

  public function __construct($stream_socket_client) {
    $this->client_stream_socket = $stream_socket_client;
  }

  public function getBadTokens() {
    return $this->badTokens;
  }

  public function send($payload, $tokenIterator, $expiry = 0) {
    if (!$this->client_stream_socket->isConnected()) {
      $this->client_stream_socket->connect();
    }

    $payload = is_array($payload) ? json_encode($payload) : $payload;
    $payload_length = mb_strlen($payload);

    $tokenBatch = array();
    $tokenBatchCount = 0;

    $this->tokensSent = 0;
    $this->badTokens = array();

    foreach ($tokenIterator as $token) {
      $tokenBatch[] = $token;
      $tokenBatchCount += 1;

      if ($tokenBatchCount === Client::FASTAPNS_BATCH_SIZE) {
        $this->_sendTokenBatch($tokenBatch, $expiry, $payload, $payload_length);

        $tokenBatch = array();
        $tokenBatchCount = 0;
      }
    }

    if ($tokenBatchCount > 0) {
      $this->_sendTokenBatch($tokenBatch, $expiry, $payload, $payload_length);
    }
  }

  private function _sendTokenBatch($tokenBatch, $expiry, $payload, $payload_length) {
    $tokenBatchCount = count($tokenBatch);
    $tokenBatchPointer = 0;

    $socket = $this->client_stream_socket;

    while ($tokenBatchPointer < $tokenBatchCount) {
      $token = $tokenBatch[$tokenBatchPointer];

      $notification_bytes = chr(1);
      $notification_bytes .= pack('N', $tokenBatchPointer);
      $notification_bytes .= pack('N', $expiry);
      $notification_bytes .= chr(0) . chr(32);
      $notification_bytes .= pack('H*', $token);
      $notification_bytes .= chr(0) . chr($payload_length);
      $notification_bytes .= $payload;

      $result = $socket->write($notification_bytes);

      if (!$result) {
        $result = $socket->retry($notification_bytes);
      }

      if (!$result) {
        $error = $socket->getError();

        if (!empty($error['identifier'])) {
          $tokenBatchPointer = $error['identifier'];

          if ($error['status'] === 8) {
            $this->badTokens[] = $tokenBatch[$tokenBatchPointer];

            $tokenBatchPointer += 1;
          }
        }
      }

      $tokenBatchPointer += 1;
    }
  }
}
