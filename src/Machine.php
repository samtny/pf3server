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

  /** @Column(type="datetime") **/
  protected $created;

  /** @Column(type="datetime") **/
  protected $updated;

  public function __construct($data = array()) {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");

    foreach ($data as $key => $val) {
      if (property_exists($this, $key)) {
        $this->{$key} = $val;
      }
    }
  }

  public function getId()
  {
    return $this->id;
  }

  public function getCreated() {
    return $this->created;
  }

  public function getUpdated() {
    return $this->updated;
  }
}
