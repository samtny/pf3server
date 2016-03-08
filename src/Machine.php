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

  public function getVenue()
  {
    return $this->venue;
  }

  public function setVenue($venue) {
    $this->venue = $venue;
  }

  public function getGame() {
    return $this->game;
  }

  public function setGame($game) {
    $this->game = $game;
  }

  public function getCreated() {
    return $this->created;
  }

  public function setCreated($created) {
    $this->created = $created;
  }

  public function getUpdated() {
    return $this->updated;
  }

  public function setUpdated($updated) {
    $this->updated = $updated;
  }
}
