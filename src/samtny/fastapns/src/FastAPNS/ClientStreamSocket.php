<?php

namespace FastAPNS;

class ClientStreamSocket {
  const FASTAPNS_CONNECTION_TIMEOUT = 5;
  const FASTAPNS_WRITE_RETRIES = 2;

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
    return $this->error;
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

  private function reconnect() {
    $this->disconnect();
    $this->connect();
  }

  public function isConnected() {
    return $this->stream_socket_client !== FALSE;
  }

  /**
   * @param $notification_bytes
   * @return bool
   */
  public function write($notification_bytes) {
    $this->error = NULL;

    try {
      return fwrite($this->stream_socket_client, $notification_bytes);
    } catch (\Exception $e) {
      return FALSE;
    }
  }

  public function retry($notification_bytes) {
    $read = array($this->stream_socket_client);
    $write = array($this->stream_socket_client);
    $except = NULL;

    stream_select($read, $write, $except, ClientStreamSocket::FASTAPNS_CONNECTION_TIMEOUT);

    if (!empty($write)) {
      return $this->write($notification_bytes);
    }

    if (!empty($read)) {
      $this->error = $this->parseError();

      if (empty($this->error['status']) || $this->error['status'] === 10) {
        $this->reconnect();
      }
    }

    if (empty($write) && empty($read)) {
      $this->reconnect();
    }

    return FALSE;
  }

  public function confirm() {
    $read = array($this->stream_socket_client);
    $write = NULL;
    $except = NULL;

    stream_select($read, $write, $except, ClientStreamSocket::FASTAPNS_CONNECTION_TIMEOUT);

    if (!empty($read)) {
      $this->error = $this->parseError();

      if (empty($this->error['status']) || $this->error['status'] === 10) {
        $this->reconnect();
      }
    }

    return FALSE;
  }

  public function parseError() {
    $bytes = fread($this->stream_socket_client, 6);

    return unpack("C1command/C1status/N1identifier", $bytes);
  }
}
