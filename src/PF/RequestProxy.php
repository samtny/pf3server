<?php

namespace PF;

class RequestProxy {
  private $queryParams;

  public function __construct($queryParams = array()) {
    $this->queryParams = $queryParams;
  }

  public function set ($key, $value) {
    $this->queryParams[$key] = $value;
  }

  public function get ($key) {
    return !empty($this->queryParams[$key]) ? $this->queryParams[$key] : NULL;
  }

  public function getQueryParams() {
    return $this->queryParams;
  }
}
