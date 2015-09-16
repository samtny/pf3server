<?php

namespace PF;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="venue")
 **/
class Venue {

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $name;

  /**
   * @OneToMany(targetEntity="Machine", mappedBy="venue")
   */
  protected $machines;

  /** @OneToMany(targetEntity="Comment", mappedBy="venue") */
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

  public function addMachine($machine) {
    $this->machines[] = $machine;
  }

  public function addComment($comment) {
    $this->comments[] = $comment;
  }
}
