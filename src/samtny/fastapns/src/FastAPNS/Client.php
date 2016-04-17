<?php

namespace FastAPNS;

class Client {
  const FASTAPNS_BATCH_SIZE = 1700;

  const FASTAPNS_BATCH_SUCCESS = 1;
  const FASTAPNS_BATCH_NEEDS_REWIND = 2;

  /**
   * @var ClientStreamSocket
   */
  private $client_stream_socket;

  private $batches;

  private $badTokens;

  public function __construct($stream_socket_client) {
    $this->client_stream_socket = $stream_socket_client;
  }

  public function getBadTokens() {
    return $this->badTokens;
  }

  public function send($payload, $tokens, $expiry = 0) {
    if (!$this->client_stream_socket->isConnected()) {
      $this->client_stream_socket->connect();
    }

    $payload = is_array($payload) ? json_encode($payload) : $payload;

    $this->batches = array();
    $this->badTokens = array();

    $batch = array();
    $currentBatchIndex = 0;

    foreach ($tokens as $token) {
      $batch[] = $token;

      if (count($batch) === Client::FASTAPNS_BATCH_SIZE) {
        $this->batches[] = $batch;

        $this->_processBatches($currentBatchIndex, $payload, $expiry);

        $batch = array();
        $currentBatchIndex += 1;
      }
    }

    if (count($batch) > 0) {
      $this->batches[] = $batch;

      $this->_processBatches($currentBatchIndex, $payload, $expiry);
    }
  }

  private function _processBatches($currentBatchIndex, $payload, $expiry) {
    $batchProcessIndex = $currentBatchIndex;
    $offset = 0;

    while ($batchProcessIndex <= $currentBatchIndex) {
      $batch = $this->batches[$batchProcessIndex];

      $tokensSent = $this->_sendBatch($batch, $payload, $expiry, $offset);

      if ($tokensSent < count($batch)) {
        $rewind = $this->_rewind($batchProcessIndex * Client::FASTAPNS_BATCH_SIZE + $tokensSent);

        $batchProcessIndex = floor($rewind / Client::FASTAPNS_BATCH_SIZE);
        $offset = $rewind - $batchProcessIndex;

        continue;
      }

      $batchProcessIndex += 1;
    }
  }

  private function _sendBatch($batch, $payload, $expiry, $offset) {
    $socket = $this->client_stream_socket;

    $tokenBatchPointer = $offset;

    $tokenBatchCount = count($batch);

    while ($tokenBatchPointer < $tokenBatchCount) {
      $token = $batch[$tokenBatchPointer];

      $notification_bytes = chr(1);
      $notification_bytes .= pack('N', $tokenBatchPointer);
      $notification_bytes .= pack('N', $expiry);
      $notification_bytes .= chr(0) . chr(32);
      $notification_bytes .= pack('H*', $token);
      $notification_bytes .= chr(0) . chr(mb_strlen($payload));
      $notification_bytes .= $payload;

      $result = $socket->write($notification_bytes);

      if ($result == ClientStreamSocket::FASTAPNS_WRITE_FAILED_WRITABLE) {
        continue;
      }

      if ($result == ClientStreamSocket::FASTAPNS_WRITE_FAILED_READABLE) {
        return $tokenBatchPointer;
      }

      if ($tokenBatchPointer == $tokenBatchCount - 1) {
        $result = $socket->status(TRUE);

        if ($result == ClientStreamSocket::FASTAPNS_WRITE_FAILED_READABLE) {
          return $tokenBatchPointer;
        }
      }

      $tokenBatchPointer += 1;
    }

    return $tokenBatchPointer;
  }

  private function _rewind($currentPointer) {
    $rewind = $currentPointer;

    $socket = $this->client_stream_socket;

    $error = $socket->read();

    if (!empty($error)) {
      $rewind = $error['identifier'];

      if ($error['status'] === 8) {
        $batchIndex = floor($rewind / Client::FASTAPNS_BATCH_SIZE);
        $tokenIndex = $rewind % Client::FASTAPNS_BATCH_SIZE;

        $this->badTokens[] = $this->batches[$batchIndex][$tokenIndex];

        $rewind += 1;
      } else if ($error['status'] === 10) {
        $socket->reconnect();
      } else {
        throw new \Exception('Unrecoverable error sending notification: please check your payload.');
      }
    } else {
      $socket->reconnect();
    }

    return $rewind;
  }
}
