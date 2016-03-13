<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity @ORM\Table(name="machine")
 * @JMS\ExclusionPolicy("none")
 **/
class Machine {

  /**
   * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
   * @JMS\Type("integer")
   */
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="Venue", inversedBy="machines")
   * @JMS\Type("string")
   */
  protected $venue;

  /**
   * @ORM\ManyToOne(targetEntity="Game")
   * @JMS\Exclude
   */
  protected $game;

  /**
   * @ORM\Column(name="`condition`", type="integer", nullable=true)
   * @JMS\Type("integer")
   */
  protected $condition;

  /**
   * @ORM\Column(type="string", nullable=true)
   * @JMS\Type("string")
   */
  protected $price;

  /**
   * @ORM\Column(type="datetime")
   * @JMS\Type("DateTime")
   */
  protected $created;

  /**
   * @ORM\Column(type="datetime")
   * @JMS\Type("DateTime")
   */
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

  /**
   * @JMS\VirtualProperty
   */
  public function getName() {
    return $this->game->getName();
  }

  /**
   * @JMS\VirtualProperty
   */
  public function getIpdb() {
    return $this->game->getIpdb();
  }

  /**
   * @JMS\VirtualProperty
   */
  public function getNew() {
    return $this->game->getNew();
  }

  /**
   * @JMS\VirtualProperty
   */
  public function getRare() {
    return $this->game->getRare();
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

  public function getCondition() {
    return $this->condition;
  }

  public function setCondition($condition) {
    $this->condition = $condition;
  }

  public function getPrice() {
    return $this->price;
  }

  public function setPrice($price) {
    $this->price = $price;
  }
}
