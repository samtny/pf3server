<?php

namespace PF;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="VenueRepository")
 * @ORM\Table(name="venue",indexes={@ORM\Index(name="latitude_longitude_idx", columns={"latitude", "longitude"})})
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("none")
 **/
class Venue {

  /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue @JMS\Type("integer") **/
  protected $id;

  /** @ORM\Column(type="string") @JMS\Type("string") **/
  protected $name;

  /** @ORM\Column(type="string") @JMS\Exclude @JMS\Type("string") */
  protected $name_clean;

  /** @ORM\Column(type="string") @JMS\Exclude @JMS\Type("string") */
  protected $name_dm;

  /** @ORM\Column(type="string", nullable=true) @JMS\Type("string") **/
  protected $street;

  /** @ORM\Column(type="string", nullable=true) @JMS\Type("string") **/
  protected $city;

  /** @ORM\Column(type="string", nullable=true) @JMS\Type("string") **/
  protected $state;

  /** @ORM\Column(type="string", nullable=true) @JMS\Type("string") **/
  protected $zipcode;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) @JMS\Type("double") */
  protected $latitude;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) @JMS\Type("double") */
  protected $longitude;

  /** @ORM\Column(type="string", nullable=true) @JMS\Type("string") **/
  protected $phone;

  /** @ORM\Column(type="string", nullable=true) @JMS\Type("string") **/
  protected $url;

  /** @ORM\Column(type="string", options={"default":"NEW"}) @JMS\Type("string") **/
  protected $status;

  /** @ORM\Column(type="string", nullable=true) @JMS\Type("string") **/
  protected $flag_reason;

  /** @ORM\Column(type="datetime") @JMS\Type("DateTime") **/
  protected $created;

  /** @ORM\Column(type="datetime") @JMS\Type("DateTime") **/
  protected $updated;

  /**
   * @ORM\OneToMany(targetEntity="Machine", mappedBy="venue", cascade={"persist", "remove", "merge"})
   * @JMS\Type("ArrayCollection<PF\Machine>")
   */
  protected $machines;

  /**
   * @ORM\OneToMany(targetEntity="Comment", mappedBy="venue", cascade={"persist", "remove", "merge"})
   * @JMS\Type("ArrayCollection<PF\Comment>")
   */
  protected $comments;

  /**
   * @ORM\PrePersist
   */
  public function prePersist() {
    $this->name_clean = StringUtil::cleanName($this->name);

    $this->name_dm = StringUtil::dmName($this->name);

    foreach ($this->machines as $machine) {
      $machine->setVenue($this);
    }
  }

  public function __construct($data = array()) {
    $this->machines = new ArrayCollection();
    $this->comments = new ArrayCollection();
  }

  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;

    $this->name_clean = StringUtil::cleanName($name);

    $this->name_dm = StringUtil::dmName($name);
  }

  public function postDeserialize($entityManager) {
    $this->setName($this->getName());

    foreach ($this->machines as $machine) {
      $machine->postDeserialize($entityManager);
    }
  }

  public function getNameClean() {
    return $this->name_clean;
  }

  public function getNameDm() {
    return $this->name_dm;
  }

  public function getStreet()
  {
    return $this->street;
  }

  public function setStreet($street)
  {
    $this->street = $street;
  }

  public function getStatus() {
    return $this->status;
  }

  public function setStatus($status) {
    $this->status = $status;
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

  /**
   * @return mixed
   */
  public function getCity() {
    return $this->city;
  }

  /**
   * @param mixed $city
   */
  public function setCity($city) {
    $this->city = $city;
  }

  /**
   * @return mixed
   */
  public function getState() {
    return $this->state;
  }

  /**
   * @param mixed $state
   */
  public function setState($state) {
    $this->state = $state;
  }

  /**
   * @return mixed
   */
  public function getZipcode() {
    return $this->zipcode;
  }

  /**
   * @param mixed $zipcode
   */
  public function setZipcode($zipcode) {
    $this->zipcode = $zipcode;
  }

  public function getLatitude() {
    return $this->latitude;
  }

  public function setLatitude($latitude) {
    $this->latitude = $latitude;
  }

  public function getLongitude() {
    return $this->longitude;
  }

  public function setLongitude($longitude) {
    $this->longitude = $longitude;
  }

  /**
   * @return mixed
   */
  public function getPhone() {
    return $this->phone;
  }

  /**
   * @param mixed $phone
   */
  public function setPhone($phone) {
    $this->phone = $phone;
  }

  /**
   * @return mixed
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @param mixed $url
   */
  public function setUrl($url) {
    $this->url = $url;
  }

  /**
   * @return mixed
   */
  public function getFlagReason() {
    return $this->flag_reason;
  }

  /**
   * @param mixed $flag_reason
   */
  public function setFlagReason($flag_reason) {
    $this->flag_reason = $flag_reason;
  }

  public function getMachines() {
    return $this->machines;
  }

  public function addMachine(Machine $machine) {
    $this->machines[] = $machine;

    $machine->setVenue($this);
  }

  public function getComments() {
    return $this->comments;
  }

  public function addComment(Comment $comment) {
    $this->comments[] = $comment;

    $comment->setVenue($this);
  }

  public function approve() {
    $this->status = "APPROVED";
  }
}
