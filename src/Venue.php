<?php

namespace PF;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

use JsonSerializable;

use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="VenueRepository")
 * @ORM\Table(name="venue",indexes={@ORM\Index(name="latitude_longitude_idx", columns={"latitude", "longitude"})})
 * @JMS\ExclusionPolicy("all")
 **/
class Venue implements JsonSerializable {

  /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
  protected $id;

  /** @ORM\Column(type="string") @JMS\Expose **/
  protected $name;

  /** @ORM\Column(type="string", nullable=true) **/
  protected $street;

  /** @ORM\Column(type="string", nullable=true) **/
  protected $city;

  /** @ORM\Column(type="string", nullable=true) **/
  protected $state;

  /** @ORM\Column(type="string", nullable=true) **/
  protected $zipcode;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $latitude;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $longitude;

  /** @ORM\Column(type="string", nullable=true) **/
  protected $phone;

  /** @ORM\Column(type="string", nullable=true) **/
  protected $url;

  /** @ORM\Column(type="string", options={"default":"NEW"}) **/
  protected $status;

  /** @ORM\Column(type="string", nullable=true) **/
  protected $flag_reason;

  /** @ORM\Column(type="datetime") **/
  protected $created;

  /** @ORM\Column(type="datetime") **/
  protected $updated;

  /**
   * @ORM\OneToMany(targetEntity="Machine", mappedBy="venue", cascade={"persist", "remove"})
   */
  protected $machines;

  /** @ORM\OneToMany(targetEntity="Comment", mappedBy="venue") */
  protected $comments;

  public function __construct($data = array()) {
    $this->status  = "NEW";
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");

    $this->machines = new ArrayCollection();
    $this->comments = new ArrayCollection();

    foreach ($data as $key => $val) {
      if (property_exists($this, $key)) {
        switch ($key) {
          case 'machines':
            $this->machines->add(new Machine($val));

            break;

          case 'comments':
            $this->machines->add(new Comment($val));

            break;

          default:
            $this->{$key} = $val;

            break;
        }
      }
    }
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

  public function addComment(Comment $comment) {
    $this->comments[] = $comment;
  }

  public function approve() {
    $this->status = "APPROVED";
  }

  public function jsonSerialize() {
    return array(
      'id' => $this->id,
      'name' => $this->name,
      'street' => $this->street,
      'city' => $this->city,
      'state' => $this->state,
      'zipcode' => $this->zipcode,
      'latitude' => $this->latitude,
      'longitude' => $this->longitude,
    );
  }
}
