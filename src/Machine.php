<?php

namespace PF;

/**
 * @Entity @Table(name="machine")
 **/
class Machine {

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @ManyToOne(targetEntity="Venue") */
  protected $venue;

  /**
   * @ManyToOne(targetEntity="Game")
   */
  protected $game;

  public function getId()
  {
    return $this->id;
  }
}
