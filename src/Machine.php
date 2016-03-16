<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity @ORM\Table(name="machine")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("none")
 **/
class Machine {

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   * @JMS\Type("integer")
   */
  protected $id;

  /**
   * @JMS\Type("integer")
   * @JMS\Accessor(getter="getIpdb")
   */
  protected $ipdb;

  /**
   * @ORM\ManyToOne(targetEntity="Game")
   * @JMS\Exclude
   */
  protected $game;

  /**
   * @ORM\ManyToOne(targetEntity="Venue", inversedBy="machines")
   * @JMS\Type("PF\Venue")
   */
  protected $venue;

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

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("integer")
   */
  public function getIpdb() {
    return !empty($this->game) ? $this->game->getId() : null;
  }

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("string")
   */
  public function getName() {
    return !empty($this->game) ? $this->game->getName() : null;
  }

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("boolean")
   */
  public function getNew() {
    return !empty($this->game) ? $this->game->getNew() : null;
  }

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("boolean")
   */
  public function getRare() {
    return !empty($this->game) ? $this->game->getRare() : null;
  }

  /**
   * @ORM\PrePersist
   */
  public function prePersist() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
  }

  /**
   * @JMS\PostDeserialize
   */
  public function postDeserialize() {
    $game = new Game();
    $game->setId($this->ipdb);

    $this->setGame($game);
  }

  public function __construct($data = array()) {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
  }

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function setIpdb($ipdb) {
    $this->ipdb = $ipdb;
  }

  public function getGame() {
    return $this->game;
  }

  public function setGame($game) {
    $this->game = $game;
  }

  public function getVenue()
  {
    return $this->venue;
  }

  public function setVenue($venue) {
    $this->venue = $venue;
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
