<?php

namespace PF;

class LogEntry {
  protected $id;
  protected $level;
  protected $message;
  protected $created;
  protected $updated;

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
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
  public function getLevel() {
    return $this->level;
  }

  /**
   * @param mixed $level
   */
  public function setLevel($level) {
    $this->level = $level;
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

}
