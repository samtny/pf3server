<?php

namespace FastAPNS;

class ClientBuilder {
  private $stream_socket_client;

  public static function create() {
    return new static();
  }

  public function setStreamSocketClient($stream_socket_client) {
    $this->stream_socket_client = $stream_socket_client;

    return $this;
  }

  public function build() {
    return new Client($this->stream_socket_client);
  }
}
