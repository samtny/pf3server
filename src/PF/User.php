<?php

namespace PF;

use Doctrine\Common\Collections\ArrayCollection;

class User {
  protected $id;
  protected $username;
  protected $password;
  protected $tokens;
  protected $created;
  protected $updated;

  public function __construct()
  {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
    $this->tokens = new ArrayCollection();
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
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * @param mixed $username
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }

  /**
   * @return mixed
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * @param mixed $password
   */
  public function setPassword($password)
  {
    $this->password = $password;
  }

  /**
   * @return mixed
   */
  public function getTokens()
  {
    return $this->tokens;
  }

  /**
   * @param mixed $tokens
   */
  public function setTokens($tokens)
  {
    $this->tokens = $tokens;
  }

  /**
   * @return \DateTime
   */
  public function getCreated()
  {
    return $this->created;
  }

  /**
   * @param \DateTime $created
   */
  public function setCreated($created)
  {
    $this->created = $created;
  }

  /**
   * @return \DateTime
   */
  public function getUpdated()
  {
    return $this->updated;
  }

  /**
   * @param \DateTime $updated
   */
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }

  public function addToken($token) {
    $this->tokens[] = $token;

    $token->setUser($this);
  }
}
