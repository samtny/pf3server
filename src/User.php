<?php

namespace PF;

/**
 * @Entity @Table(name="user")
 **/
class User {
  /** @Id @GeneratedValue @Column(type="integer") **/
  protected $id;

  /** @Column(type="string") **/
  protected $name;

  public function getId()
  {
    return $this->id;
  }
}
