<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="GameRepository")
 * @ORM\Entity @ORM\Table(name="game")
 **/
class Game {

  /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
  protected $id;

  /** @ORM\Column(type="string") */
  protected $name;

  /** @ORM\Column(type="string", nullable=true) */
  protected $abbreviation;

  /** @ORM\Column(type="string", nullable=true) */
  protected $year;

  /** @ORM\Column(type="integer", nullable=true) */
  protected $ipdb;

  /** @ORM\Column(type="datetime") **/
  protected $created;

  /** @ORM\Column(type="datetime") **/
  protected $updated;

  public function __construct($data = array()) {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");

    foreach ($data as $key => $val) {
      if (property_exists($this, $key)) {
        $this->{$key} = $val;
      }
    }
  }

  public function getId()
  {
    return $this->id;
  }

  /**
   * @return mixed
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param mixed $name
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * @return mixed
   */
  public function getYear() {
    return $this->year;
  }

  /**
   * @param mixed $year
   */
  public function setYear($year) {
    $this->year = $year;
  }

  /**
   * @return mixed
   */
  public function getIpdb() {
    return $this->ipdb;
  }

  /**
   * @param mixed $ipdb
   */
  public function setIpdb($ipdb) {
    $this->ipdb = $ipdb;
  }

  /**
   * @return mixed
   */
  public function getAbbreviation() {
    return $this->abbreviation;
  }

  /**
   * @param mixed $abbreviation
   */
  public function setAbbreviation($abbreviation) {
    $this->abbreviation = $abbreviation;
  }

  /**
   * @return mixed
   */
  public function getCreated() {
    return $this->created;
  }

  /**
   * @param mixed $created
   */
  public function setCreated($created) {
    $this->created = $created;
  }

  /**
   * @return mixed
   */
  public function getUpdated() {
    return $this->updated;
  }

  /**
   * @param mixed $updated
   */
  public function setUpdated($updated) {
    $this->updated = $updated;
  }
}
