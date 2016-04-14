<?php

namespace FastAPNS;

class ClientStreamSocket {
  private $local_cert;
  private $passphrase;
  private $host;
  private $port;

  private $stream_socket_client;

  private $error;

  public function __construct($local_cert, $passphrase = '', $host = Client::FASTAPNS_GATEWAY_HOST, $port = Client::FASTAPNS_GATEWAY_PORT) {
    $this->local_cert = $local_cert;
    $this->passphrase = $passphrase;
    $this->host = $host;
    $this->port = $port;
  }

  public function getError() {
    return $error;
  }

  public function connect() {
    $streamContext = stream_context_create();

    stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->local_cert);
    stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->passphrase);

    $this->stream_socket_client = stream_socket_client('ssl://' . $this->host . ':' . $this->port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);

    if (!empty($error)) {
      throw new \Exception('Error creating stream socket client: ' . $errorString);
    }
  }

  public function disconnect() {
    fclose($this->stream_socket_client);
  }

  public function isConnected() {
    return $this->stream_socket_client !== FALSE;
  }

  public function write($notification_bytes, $nestLevel = 0) {
    try {
      fwrite($this->stream_socket_client, $notification_bytes);
    } catch (\Exception $e) {
      $read = array($this->stream_socket_client);
      $write = array($this->stream_socket_client);
      $except = NULL;

      stream_select($read, $write, $except, Client::FASTAPNS_CONNECTION_TIMEOUT);

      if (!empty($write) && $nestLevel < Client::FASTAPNS_WRITE_RETRIES) {
        $this->write($notification_bytes, $nestLevel + 1);
      } else if (!empty($read)) {
        $this->read();
      } else {
        $this->_reconnect();
      }
    }
  }

  public function finish() {
    $read = array($this->stream_socket_client);
    // TODO: implement batch rewind and be prepared to write immediately;
    $write = NULL;
    $except = NULL;

    stream_select($read, $write, $except, Client::FASTAPNS_CONNECTION_TIMEOUT);

    if (!empty($read)) {
      $this->read();
    }
  }

  public function read() {
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
    $this->error = unpack("C1command/C1status/N1identifier", $bytes);

    switch ($this->error['status']) {
      case 10:
        //$this->_rewind($this->error['identifier']);
        $this->_reconnect();

        break;
      case 8:
        //$this->_rewind($this->error['identifier']);
        $this->tokenBatchPointerBadToken = TRUE;

        break;
      default:
        if ($this->error['identifier'] === 0) {
          throw new \Exception('Could not send any notifications; check your payload for correctness');
        } else {
          //$this->_rewind($this->error['identifier']);
          $this->_reconnect();
        }

        break;
    }
  }

  private function _reconnect() {
    $this->disconnect();
    $this->connect();
  }
}
