<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity @ORM\Table(name="comment")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("none")
 **/
class Comment {

  /**
   * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
   * @JMS\Type("integer")
   */
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="Venue", inversedBy="comments")
   * @JMS\Type("PF\Venue")
   */
  protected $venue;

  /**
   * @ORM\Column(type="string")
   * @JMS\Type("string")
   * @JMS\Groups({"create","update"})
   */
  protected $text;

  /**
   * @ORM\Column(type="datetime", options={"default":0})
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

  public function __construct($data = array()) {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
  }

  public function getId()
  {
    return $this->id;
  }

  public function getVenue() {
    return $this->venue;
  }

  public function setVenue($venue) {
    $this->venue = $venue;
  }

  public function getText() {
    return $this->text;
  }

  public function setText($text) {
    $this->text = $text;
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
