<?php

namespace PF;

class StatRecord {

  protected $id;
  protected $created;
  protected $updated;
  protected $path;
  protected $method;
  protected $params;

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
  }

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function getCreated() {
    return $this->created;
  }

  public function setCreated($created) {
    $this->created = $created;
  }

  public function getUpdated() {
    return $this->updated;
  }

  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getPath() {
    return $this->path;
  }

  public function setPath($path) {
    $this->path = $path;
  }

  public function getMethod() {
    return $this->method;
  }

  public function setMethod($method) {
    $this->method = $method;
  }

  public function getParams() {
    return $this->params;
  }

  public function setParams($params) {
    $this->params = $params;
  }

}
