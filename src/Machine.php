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
   * @JMS\Groups({"read"})
   */
  protected $id;

  /**
   * @JMS\Type("integer")
   * @JMS\Accessor(getter="getIpdb", setter="setIpdb")
   * @JMS\Groups({"create","read","update"})
   */
  protected $ipdb;

  /**
   * @ORM\ManyToOne(targetEntity="Game")
   * @JMS\Exclude
   */
  protected $game;

  /**
   * @ORM\ManyToOne(targetEntity="Venue", inversedBy="machines")
   * @JMS\Exclude
   */
  protected $venue;

  /**
   * @ORM\Column(name="`condition`", type="integer", nullable=true)
   * @JMS\Type("integer")
   * @JMS\Groups({"create","read","update"})
   */
  protected $condition;

  /**
   * @ORM\Column(type="string", nullable=true)
   * @JMS\Type("string")
   * @JMS\Groups({"create","read","update"})
   */
  protected $price;

  /** @ORM\Column(type="boolean", options={"default":false}) @JMS\Type("boolean") @JMS\Groups({"create","update"}) */
  protected $deleted;

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
   * @JMS\Groups({"read"})
   */
  public function getIpdb() {
    return !empty($this->game) ? $this->game->getId() : null;
  }

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("string")
   * @JMS\Groups({"read"})
   */
  public function getName() {
    return !empty($this->game) ? $this->game->getName() : null;
  }

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("boolean")
   * @JMS\Groups({"read"})
   */
  public function getNew() {
    return !empty($this->game) ? $this->game->getNew() : null;
  }

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("boolean")
   * @JMS\Groups({"read"})
   */
  public function getRare() {
    return !empty($this->game) ? $this->game->getRare() : null;
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
    $this->deleted = false;
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
