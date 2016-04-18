<?php

namespace FastAPNS;

class ClientBuilder {
  private $stream_socket_client;
  private $local_cert;
  private $passphrase;
  private $host;
  private $port;
  private $batch_size;

  const FASTAPNS_BATCH_SIZE_DEFAULT = 1700;

  public static function create() {
    return new static();
  }

  public function __construct() {
    $this->host = ClientStreamSocket::FASTAPNS_DEFAULT_GATEWAY_HOST;
    $this->port = ClientStreamSocket::FASTAPNS_DEFAULT_GATEWAY_PORT;
  }

  public function setStreamSocketClient($stream_socket_client) {
    $this->stream_socket_client = $stream_socket_client;

    return $this;
  }

  public function setLocalCert($local_cert) {
    $this->local_cert = $local_cert;

    return $this;
  }

  public function setPassphrase($passphrase) {
    $this->passphrase = $passphrase;

    return $this;
  }

  public function setHost($host) {
    $this->host = $host;

    return $this;
  }

  public function setPort($port) {
    $this->port = $port;

    return $this;
  }

  public function setBatchSize($batch_size) {
    $this->batch_size = $batch_size;

    return $this;
  }

  public function build() {
    if (!$this->stream_socket_client) {
      $this->stream_socket_client = new ClientStreamSocket(
        $this->local_cert,
        $this->passphrase,
        $this->host,
        $this->port
      );
    }

    if (!$this->batch_size) {
      $this->batch_size = Client::FASTAPNS_BATCH_SIZE_DEFAULT;
    }

    return new Client($this->stream_socket_client, $this->batch_size);
  }
}
