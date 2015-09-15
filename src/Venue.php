<?php
// src/Venue.php
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

  public function __construct() {
    $this->machines = new ArrayCollection();
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
