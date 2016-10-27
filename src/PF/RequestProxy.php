<?php

namespace PF;

class RequestProxy {
  private $vars;

  public function __construct($vars = array()) {
    $this->vars = $vars;
  }

  public function set ($key, $value) {
    $this->vars[$key] = $value;
  }

  public function get ($key) {
    return !empty($this->vars[$key]) ? $this->vars[$key] : NULL;
  }
}
