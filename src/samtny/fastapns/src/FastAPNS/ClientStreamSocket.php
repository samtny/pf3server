<?php

namespace FastAPNS;

class ClientStreamSocket {
  const FASTAPNS_DEFAULT_GATEWAY_HOST = 'gateway.push.apple.com';
  const FASTAPNS_DEFAULT_GATEWAY_PORT = 2195;
  const FASTAPNS_STATUS_TIMEOUT = 3;

  const FASTAPNS_WRITE_SUCCESS = 1;
  const FASTAPNS_STATUS_WRITABLE = 2;
  const FASTAPNS_STATUS_READABLE = 3;
  const FASTAPNS_STATUS_NONE = 4;

  private $local_cert;
  private $passphrase;
  private $host;
  private $port;

  private $stream_socket_client;

  public function __construct($local_cert, $passphrase = '', $host = ClientStreamSocket::FASTAPNS_DEFAULT_GATEWAY_HOST, $port = ClientStreamSocket::FASTAPNS_DEFAULT_GATEWAY_PORT) {
    $this->local_cert = $local_cert;
    $this->passphrase = $passphrase;
    $this->host = $host;
    $this->port = $port;
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

  public function reconnect() {
    $this->disconnect();
    $this->connect();
  }

  public function isConnected() {
    return !empty($this->stream_socket_client);
  }

  /**
   * @param $notification_bytes
   * @return bool
   */
  public function write($notification_bytes) {
    try {
      fwrite($this->stream_socket_client, $notification_bytes);

      return ClientStreamSocket::FASTAPNS_WRITE_SUCCESS;
    } catch (\Exception $e) {
      return $this->status();
    }
  }

  public function read() {
    $bytes = fread($this->stream_socket_client, 6);

    return unpack("C1command/C1status/N1identifier", $bytes);
  }

  public function status($readOnly = FALSE) {
    $read = array($this->stream_socket_client);
    $write = $readOnly ? NULL : array($this->stream_socket_client);
    $except = NULL;

    stream_select($read, $write, $except, ClientStreamSocket::FASTAPNS_STATUS_TIMEOUT);

    if (!empty($write)) {
      return ClientStreamSocket::FASTAPNS_STATUS_WRITABLE;
    }

    if (!empty($read)) {
      return ClientStreamSocket::FASTAPNS_STATUS_READABLE;
    }

    return ClientStreamSocket::FASTAPNS_STATUS_NONE;
  }
}
