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
   * @JMS\Type("PF\Venue")
   */
  protected $venue;

  /**
   * @ORM\ManyToOne(targetEntity="Game")
   * @JMS\Exclude
   */
  protected $game;

  /**
   * @JMS\Type("string")
   */
  protected $name;

  /**
   * @JMS\Type("integer")
   */
  protected $ipdb;

  /**
   * @JMS\Type("boolean")
   */
  protected $new;

  /**
   * @JMS\Type("boolean")
   */
  protected $rare;

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

  public function postDeserialize($entityManager) {
    if (empty($this->game)) {
      if (!empty($this->ipdb)) {
        $this->setGame($entityManager->getRepository('\PF\Game')->findOneBy(array('ipdb' => $this->ipdb)));
      } else if (!empty($this->name)) {
        $this->setGame($entityManager->getRepository('\PF\Game')->findOneBy(array('name' => $this->name)));
      }
    }
  }

  /**
   * @JMS\VirtualProperty
   */
  public function getNew() {
    return !empty($this->game) ? $this->game->getNew() : null;
  }

  /**
   * @JMS\VirtualProperty
   */
  public function getRare() {
    return !empty($this->game) ? $this->game->getRare() : null;
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
