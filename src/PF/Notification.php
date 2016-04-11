<?php

namespace PF;

class Notification {
  protected $id;
  protected $token;
  protected $app;
  protected $global;
  protected $message;
  protected $queryParams;
  protected $status;
  protected $created;
  protected $updated;

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
    $this->status = 'NEW';
  }

  /**
   * @return mixed
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * @return mixed
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * @param mixed $token
   */
  public function setToken($token) {
    $this->token = $token;
  }

  /**
   * @return mixed
   */
  public function getGlobal()
  {
    return $this->global;
  }

  /**
   * @param mixed $global
   */
  public function setGlobal($global)
  {
    $this->global = $global;
  }

  /**
   * @return mixed
   */
  public function getApp()
  {
    return $this->app;
  }

  /**
   * @param mixed $app
   */
  public function setApp($app)
  {
    $this->app = $app;
  }

  /**
   * @return mixed
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @param mixed $message
   */
  public function setMessage($message) {
    $this->message = $message;
  }

  /**
   * @return mixed
   */
  public function getQueryParams() {
    return $this->queryParams;
  }

  /**
   * @param mixed $queryParams
   */
  public function setQueryParams($queryParams) {
    $this->queryParams = $queryParams;
  }

  /**
   * @return mixed
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * @param mixed $status
   */
  public function setStatus($status) {
    $this->status = $status;
  }

  /**
   * @return mixed
   */
  public function getCreated() {
    return $this->created;
  }

  /**
   * @param mixed $created
   */
  public function setCreated($created) {
    $this->created = $created;
  }

  /**
   * @return mixed
   */
  public function getUpdated() {
    return $this->updated;
  }

  /**
   * @param mixed $updated
   */
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function archive($migration = false) {
    $this->status = "DELETED";

    if (!$migration) {
      $this->updated = new \DateTime("now");
    }
  }
}
