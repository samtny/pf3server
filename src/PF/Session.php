<?php

namespace PF;

class Session {
  protected $id;
  protected $user;
  protected $created;
  protected $updated;

  public function __construct()
  {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id)
  {
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
  public function getCreated()
  {
    return $this->created;
  }

  /**
   * @param mixed $created
   */
  public function setCreated($created)
  {
    $this->created = $created;
  }

  /**
   * @return mixed
   */
  public function getUpdated()
  {
    return $this->updated;
  }

  /**
   * @param mixed $updated
   */
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
}
