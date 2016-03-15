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
   * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
   * @JMS\Type("integer")
   */
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="Venue", inversedBy="machines")
   * @JMS\Type("PF\Venue")
   */
  protected $venue;

  /**
   * @ORM\ManyToOne(targetEntity="Game")
   * @JMS\Exclude
   */
  protected $game;

  /**
   * @JMS\Type("integer")
   * @JMS\Accessor(getter="getIpdb")
   */
  protected $ipdb;

  /**
   * @JMS\Type("string")
   * @JMS\Accessor(getter="getName")
   */
  protected $name;

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
   * @JMS\Exclude
   */
  protected $created;

  /**
   * @ORM\Column(type="datetime")
   * @JMS\Type("DateTime")
   * @JMS\Exclude
   */
  protected $updated;

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("string")
   */
  public function getName() {
    //return $this->game->getName();
  }

  /**
   * @JMS\VirtualProperty
   * @JMS\Type("integer")
   */
  public function getIpdb() {
    //return $this->game->getIpdb();
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

  public function __construct($data = array()) {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
  }

  public function postDeserialize($entityManager) {
    if (empty($this->game)) {
      if (!empty($this->ipdb)) {
        $this->setGame($entityManager->getRepository('\PF\Game')->findOneBy(array('ipdb' => $this->ipdb)));
      } else if (!empty($this->name)) {
        $this->setGame($entityManager->getRepository('\PF\Game')->findOneBy(array('name' => $this->name)));
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
