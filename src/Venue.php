<?php

namespace PF;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="products")
 **/
class Venue {

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $name;

  protected $machines;
  protected $comments;

  public function __construct() {
    $this->machines = new ArrayCollection();
    $this->comments = new ArrayCollection();
  }

  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }
}
