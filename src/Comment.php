<?php

namespace PF;

/**
 * @Entity @Table(name="comment")
 **/
class Comment {

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @ManyToOne(targetEntity="Venue") */
  protected $venue;

  public function getId()
  {
    return $this->id;
  }
}
