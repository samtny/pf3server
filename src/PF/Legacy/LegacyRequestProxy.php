<?php

namespace PF\Legacy;

class LegacyRequestProxy {
  private $params;

  public function __construct() {
    $this->params = array();
  }

  public function set($key, $value) {
    $this->params[$key] = $value;
  }

  public function get($key) {
    return !empty($this->params[$key]) ? $this->params[$key] : NULL;
  }
}
