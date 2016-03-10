<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="comment")
 **/
class Comment {

  /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
  protected $id;

  /** @ORM\ManyToOne(targetEntity="Venue", inversedBy="comments") */
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
