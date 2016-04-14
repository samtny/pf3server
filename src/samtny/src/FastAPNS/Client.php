<?php

namespace FastAPNS;

class Client {
  const FASTAPNS_GATEWAY_HOST = 'gateway.push.apple.com';
  const FASTAPNS_GATEWAY_PORT = 2195;
  const FASTAPNS_BATCH_SIZE = 1700;
  const FASTAPNS_CONNECTION_TIMEOUT = 5;
  const FASTAPNS_WRITE_RETRIES = 2;

  private $stream_socket_client;
  private $local_cert;
  private $passphrase;
  private $host;
  private $port;
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

  public function __construct($local_cert, $passphrase = '', $host = Client::FASTAPNS_GATEWAY_HOST, $port = Client::FASTAPNS_GATEWAY_PORT) {
    $this->local_cert = $local_cert;
    $this->passphrase = $passphrase;
    $this->host = $host;
    $this->port = $port;
    $this->badTokens = array();
  }

  public function connect() {
    $streamContext = stream_context_create();

    stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->local_cert);
    stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->passphrase);

    $this->stream_socket_client = stream_socket_client('ssl://' . $this->host . ':' . $this->port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);

    !empty($error) && throw new \Exception('Error creating stream socket client: ' . $errorString);
  }

  public function disconnect() {
    fclose($this->stream_socket_client);
  }

  public function send($payload, $tokenIterator, $expiry = 0) {
    if (!$this->stream_socket_client) {
      $this->connect();
    }

    $this->payload = is_array($payload) ? json_encode($payload) : $payload;
    $this->payload_length = mb_strlen($payload);
    $this->tokenIterator = $tokenIterator;
    $this->expiry = $expiry;

    $this->tokenBatch = array();
    $this->tokenBatchCount = 0;
    $this->tokensSent = 0;

    foreach ($tokenIterator as $token) {
      $this->tokenBatch[] = preg_replace('/\s|<|>/', '', $token);
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

      if (!$this->tokenBatchPointerBadToken) {
        $notification_bytes = chr(1);
        $notification_bytes .= pack('N', $this->tokenBatchPointer);
        $notification_bytes .= pack('N', $this->expiry);
        $notification_bytes .= chr(0) . chr(32);
        $notification_bytes .= pack('H*', $token);
        $notification_bytes .= chr(0) . chr($this->payload_length);
        $notification_bytes .= $this->payload;

        $this->_write($notification_bytes);

        $this->tokensSent += 1;

        if ($this->tokenBatchPointer === $this->tokenBatchCount - 1) {
          $read = array($this->stream_socket_client);
          // TODO: implement batch rewind and be prepared to write immediately;
          $write = NULL;
          $except = NULL;

          stream_select($read, $write, $except, Client::FASTAPNS_CONNECTION_TIMEOUT);

          if (!empty($read)) {
            $this->_read();
          }
        }
      } else {
        $this->badTokens[] = $token;

        $this->tokenBatchPointerBadToken = FALSE;
      }

      $this->tokenBatchPointer += 1;
    }
  }

  private function _write($notification_bytes, $nestLevel = 0) {
    try {
      fwrite($this->stream_socket_client, $notification_bytes);
    } catch (\Exception $e) {
      $read = array($this->stream_socket_client);
      $write = array($this->stream_socket_client);
      $except = NULL;

      stream_select($read, $write, $except, Client::FASTAPNS_CONNECTION_TIMEOUT);

      if (!empty($write) && $nestLevel < Client::FASTAPNS_WRITE_RETRIES) {
        $this->_write($notification_bytes, $nestLevel + 1);
      } else if (!empty($read)) {
        $this->_read();
      } else {
        $this->_reconnect();
      }
    }
  }

  private function _read() {
    $bytes = fread($this->stream_socket_client, 6);

    switch (strlen($bytes)) {
      case 0:
        $this->_reconnect();

        break;
      case 6:
        $this->_parseErrorResponse($bytes);

        break;
      default:
        $this->_reconnect();

        break;
    }
  }

  private function _parseErrorResponse($bytes) {
    $error = unpack("C1command/C1status/N1identifier", $bytes);

    switch ($error['status']) {
      case 10:
        $this->_rewind($bytes['identifier']);
        $this->_reconnect();

        break;
      case 8:
        $this->_rewind($bytes['identifier']);
        $this->tokenBatchPointerBadToken = TRUE;

        break;
      default:
        if ($bytes['identifier'] === 0) {
          throw new \Exception('Could not send any notifications; check your payload for correctness');
        } else {
          $this->_rewind($bytes['identifier']);
          $this->_reconnect();
        }

        break;
    }
  }

  private function _rewind($pointer) {
    $this->tokensSent -= $this->tokenBatchPointer - $pointer;

    $this->tokenBatchPointer = $pointer;
  }

  private function _reconnect() {
    $this->disconnect();
    $this->connect();
  }
}
