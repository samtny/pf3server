<?php

namespace PF;

/**
 * @Entity @Table(name="game")
 **/
class Game {

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") */
  protected $name;

  /** @Column(type="string", nullable=true) */
  protected $abbreviation;

  /** @Column(type="string", nullable=true) */
  protected $year;

  /** @Column(type="integer", nullable=true) */
  protected $ipdb;

  /** @Column(type="datetime") **/
  protected $created;

  /** @Column(type="datetime") **/
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
