<?php

namespace PF;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="VenueRepository")
 * @Table(name="venue")
 **/
class Venue {

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $name;

  /** @Column(type="string", nullable=true) **/
  protected $street;

  /** @Column(type="string", nullable=true) **/
  protected $city;

  /** @Column(type="string", nullable=true) **/
  protected $state;

  /** @Column(type="string", nullable=true) **/
  protected $zipcode;

  /** @Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $latitude;

  /** @Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $longitude;

  /** @Column(type="string", nullable=true) **/
  protected $phone;

  /** @Column(type="string", nullable=true) **/
  protected $url;

  /** @Column(type="string", options={"default":"NEW"}) **/
  protected $status;

  /** @Column(type="string", nullable=true) **/
  protected $flag_reason;

  /** @Column(type="datetime") **/
  protected $created;

  /** @Column(type="datetime") **/
  protected $updated;

  /**
   * @OneToMany(targetEntity="Machine", mappedBy="venue", cascade={"persist", "remove"})
   */
  protected $machines;

  /** @OneToMany(targetEntity="Comment", mappedBy="venue") */
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
}
