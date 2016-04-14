<?php

namespace FastAPNS;

class Client {
  const FASTAPNS_GATEWAY_HOST = 'gateway.push.apple.com';
  const FASTAPNS_GATEWAY_PORT = 2195;
  const FASTAPNS_BATCH_SIZE = 1700;
  const FASTAPNS_CONNECTION_TIMEOUT = 5;
  const FASTAPNS_WRITE_RETRIES = 2;

  /**
   * @var ClientStreamSocket
   */
  private $client_stream_socket;

  private $payload;
  private $payload_length;
  private $tokenIterator;
  private $expiry;

  private $tokenBatch;
  private $tokenBatchCount;
  private $tokenBatchPointer = 0;
  private $tokenBatchPointerBadToken = FALSE;

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

    $this->payload = is_array($payload) ? json_encode($payload) : $payload;
    $this->payload_length = mb_strlen($this->payload);
    $this->tokenIterator = $tokenIterator;
    $this->expiry = $expiry;

    $this->tokenBatch = array();
    $this->tokenBatchCount = 0;
    $this->tokensSent = 0;
    $this->badTokens = array();

    foreach ($tokenIterator as $token) {
      $this->tokenBatch[] = $token;
      $this->tokenBatchCount += 1;

      if ($this->tokenBatchCount === Client::FASTAPNS_BATCH_SIZE) {
        $this->_sendTokenBatch();

        $this->tokenBatchCount = 0;
      }
    }

    if ($this->tokenBatchCount > 0) {
      $this->_sendTokenBatch();
    }
  }

  private function _sendTokenBatch() {
    $this->tokenBatchPointer = 0;

    while ($this->tokenBatchPointer < $this->tokenBatchCount) {
      $token = $this->tokenBatch[$this->tokenBatchPointer];

      $notification_bytes = chr(1);
      $notification_bytes .= pack('N', $this->tokenBatchPointer);
      $notification_bytes .= pack('N', $this->expiry);
      $notification_bytes .= chr(0) . chr(32);
      $notification_bytes .= pack('H*', $token);
      $notification_bytes .= chr(0) . chr($this->payload_length);
      $notification_bytes .= $this->payload;

      $this->client_stream_socket->write($notification_bytes);

      if (!$this->client_stream_socket->getError()) {
        $this->tokensSent += 1;

        if ($this->tokenBatchPointer === $this->tokenBatchCount - 1) {
          $this->client_stream_socket->finish();
        }


      } else {
        $this->_rewind($identifier)
        $identifier = $this->client_stream_socket->getError()['identifier'];

        $this->badTokens[] = $this->tokenBatch[$identifier];

        $this->tokenBatchPointer = $identifier;
      }

      $this->tokenBatchPointer += 1;
    }
  }

  private function _rewind($pointer) {
    $this->tokensSent -= $this->tokenBatchPointer - $pointer;

    $this->tokenBatchPointer = $pointer - 1;
  }
}
