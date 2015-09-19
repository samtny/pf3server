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

  public function __construct($data = array()) {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");

    foreach ($data as $key => $val) {
      if (property_exists($this, $key)) {
        $this->{$key} = $val;
      }
    }
  }

  public function getCreated() {
    return $this->created;
  }

  public function getUpdated() {
    return $this->updated;
  }

  public function getId()
  {
    return $this->id;
  }
}
