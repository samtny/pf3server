<?php

namespace PF;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="VenueRepository")
 * @Table(name="venue")
 **/
class Venue {

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="string", options={"default":"NEW"}) **/
  protected $status;

  /** @Column(type="datetime") **/
  protected $created;

  /**
   * @OneToMany(targetEntity="Machine", mappedBy="venue")
   */
  protected $machines;

  /** @OneToMany(targetEntity="Comment", mappedBy="venue") */
  protected $comments;

  public function __construct() {
    $this->machines = new ArrayCollection();
    $this->comments = new ArrayCollection();

    $this->status = "NEW";
    $this->created = new \DateTime("now");
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

  public function getStatus() {
    return $this->status;
  }

  public function setStatus($status) {
    $this->status = $status;
  }

  public function addMachine($machine) {
    $this->machines[] = $machine;
  }

  public function addComment($comment) {
    $this->comments[] = $comment;
  }

  public function approve() {
    $this->status = "APPROVED";
  }
}
