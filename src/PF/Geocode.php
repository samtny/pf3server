<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="GeocodeRepository")
 * @ORM\Table(name="geocode")
 **/
class Geocode {
  /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
  protected $id;

  /** @ORM\Column(type="string") **/
  protected $string;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $coordinate_latitude;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $coordinate_longitude;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $southwest_latitude;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $southwest_longitude;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $northeast_latitude;

  /** @ORM\Column(type="decimal", precision=10, scale=7, nullable=true) */
  protected $northeast_longitude;

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
}
