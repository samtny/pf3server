<?php

namespace PF;

use Doctrine\Common\Collections\ArrayCollection;

use PF\Utilities\StringUtil;

class Venue {
  protected $id;
  protected $name;
  protected $name_clean;
  protected $name_dm;
  protected $street;
  protected $city;
  protected $state;
  protected $zipcode;
  protected $latitude;
  protected $longitude;
  protected $phone;
  protected $url;
  protected $status;
  protected $flag_reason;
  protected $created_token;
  protected $updated_token;
  protected $created;
  protected $updated;
  protected $legacy_key;

  protected $machines;
  protected $comments;

  public function __construct() {
    $this->machines = new ArrayCollection();
    $this->comments = new ArrayCollection();
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
    $this->status = 'NEW';
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

  public function getCreatedToken() {
    return $this->created_token;
  }

  public function setCreatedToken($created_token) {
    $this->created_token = $created_token;
  }

  public function getUpdatedToken() {
    return $this->updated_token;
  }

  public function setUpdatedToken($updated_token) {
    $this->updated_token = $updated_token;
  }
  
  public function getUpdated() {
    return $this->updated;
  }

  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getCity() {
    return $this->city;
  }

  public function setCity($city) {
    $this->city = $city;
  }

  public function getState() {
    return $this->state;
  }

  public function setState($state) {
    $this->state = $state;
  }

  public function getZipcode() {
    return $this->zipcode;
  }

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

  public function getPhone() {
    return $this->phone;
  }

  public function setPhone($phone) {
    $this->phone = $phone;
  }

  public function getUrl() {
    return $this->url;
  }

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getFlagReason() {
    return $this->flag_reason;
  }

  public function setFlagReason($flag_reason) {
    $this->flag_reason = $flag_reason;
  }

  public function getMachines() {
    return $this->machines;
  }

  public function addMachine(Machine $machine, $migration = false) {
    $this->machines[] = $machine;

    $machine->setVenue($this);

    if (!$migration) {
      $this->updated = new \DateTime("now");
    }
  }

  public function getComments() {
    return $this->comments;
  }

  public function addComment(Comment $comment, $migration = false) {
    $this->comments[] = $comment;

    $comment->setVenue($this);

    if (!$migration) {
      $this->updated = new \DateTime("now");
    }
  }

  public function approve($migration = false) {
    $this->status = "APPROVED";

    if (!$migration) {
      $this->updated = new \DateTime("now");
    }
  }

  public function delete($migration = false) {
    $this->status = "DELETED";

    if (!$migration) {
      $this->updated = new \DateTime("now");
    }
  }

  public function getLegacyKey() {
    return $this->legacy_key;
  }

  public function setLegacyKey($legacy_key) {
    $this->legacy_key = $legacy_key;
  }
}
