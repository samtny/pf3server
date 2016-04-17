<?php

namespace FastAPNS;

class ClientBuilder {
  private $stream_socket_client;
  private $batch_size;

  const FASTAPNS_BATCH_SIZE_DEFAULT = 1700;

  public static function create() {
    return new static();
  }

  public function setStreamSocketClient($stream_socket_client) {
    $this->stream_socket_client = $stream_socket_client;

    return $this;
  }

  public function setBatchSize($batch_size) {
    $this->batch_size = $batch_size;

    return $this;
  }

  public function build() {
    if (!$this->batch_size) {
      $this->batch_size = Client::FASTAPNS_BATCH_SIZE_DEFAULT;
    }

    return new Client($this->stream_socket_client, $this->batch_size);
  }
}
