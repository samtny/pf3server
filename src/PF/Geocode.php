<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

class Geocode {
  protected $id;
  protected $string;
  protected $coordinate_latitude;
  protected $coordinate_longitude;
  protected $southwest_latitude;
  protected $southwest_longitude;
  protected $northeast_latitude;
  protected $northeast_longitude;
  protected $created;
  protected $updated;

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
  }

  public function getId() {
    return $this->id;
  }

  public function getString() {
    return $this->string;
  }

  public function setString($string) {
    $this->string = $string;
  }

  /**
   * @return mixed
   */
  public function getCoordinateLatitude() {
    return $this->coordinate_latitude;
  }

  /**
   * @param mixed $coordinate_latitude
   */
  public function setCoordinateLatitude($coordinate_latitude) {
    $this->coordinate_latitude = $coordinate_latitude;
  }

  /**
   * @return mixed
   */
  public function getCoordinateLongitude() {
    return $this->coordinate_longitude;
  }

  /**
   * @param mixed $coordinate_longitude
   */
  public function setCoordinateLongitude($coordinate_longitude) {
    $this->coordinate_longitude = $coordinate_longitude;
  }

  /**
   * @return mixed
   */
  public function getSouthwestLatitude() {
    return $this->southwest_latitude;
  }

  /**
   * @param mixed $southwest_latitude
   */
  public function setSouthwestLatitude($southwest_latitude) {
    $this->southwest_latitude = $southwest_latitude;
  }

  /**
   * @return mixed
   */
  public function getSouthwestLongitude() {
    return $this->southwest_longitude;
  }

  /**
   * @param mixed $southwest_longitude
   */
  public function setSouthwestLongitude($southwest_longitude) {
    $this->southwest_longitude = $southwest_longitude;
  }

  /**
   * @return mixed
   */
  public function getNortheastLatitude() {
    return $this->northeast_latitude;
  }

  /**
   * @param mixed $northeast_latitude
   */
  public function setNortheastLatitude($northeast_latitude) {
    $this->northeast_latitude = $northeast_latitude;
  }

  /**
   * @return mixed
   */
  public function getNortheastLongitude() {
    return $this->northeast_longitude;
  }

  /**
   * @param mixed $northeast_longitude
   */
  public function setNortheastLongitude($northeast_longitude) {
    $this->northeast_longitude = $northeast_longitude;
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
