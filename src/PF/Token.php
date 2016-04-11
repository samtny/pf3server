<?php

namespace PF;

class Token {
  protected $id;
  protected $user;
  protected $token;
  protected $app;
  protected $status;
  protected $created;
  protected $updated;

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
    $this->status = 'VALID';
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
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @param mixed $user
   */
  public function setUser($user)
  {
    $this->user = $user;
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

  public function getApp() {
    return $this->app;
  }

  public function setApp($app) {
    $this->app = $app;
  }

  /**
   * @return mixed
   */
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * @param mixed $status
   */
  public function setStatus($status)
  {
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

  public function flag() {
    $this->status = 'FLAGGED';
  }
}
