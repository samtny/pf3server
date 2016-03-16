<?php

namespace PF;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="GameRepository")
 * @ORM\Entity @ORM\Table(name="game")
 **/
class Game {

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   */
  protected $id;

  /** @ORM\Column(type="string") */
  protected $name;

  /** @ORM\Column(type="string") */
  protected $name_clean;

  /** @ORM\Column(type="string") */
  protected $name_dm;

  /** @ORM\Column(type="string", nullable=true) */
  protected $abbreviation;

  /** @ORM\Column(type="string", nullable=true) */
  protected $year;

  /** @ORM\Column(type="boolean", nullable=true) */
  protected $new;

  /** @ORM\Column(type="boolean", nullable=true) */
  protected $rare;

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

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function getName() {
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

  public function getYear() {
    return $this->year;
  }

  public function setYear($year) {
    $this->year = $year;
  }

  public function getAbbreviation() {
    return $this->abbreviation;
  }

  public function setAbbreviation($abbreviation) {
    $this->abbreviation = $abbreviation;
  }

  public function getNew() {
    return $this->new;
  }

  public function setNew($new) {
    $this->new = $new;
  }

  public function getRare() {
    return $this->rare;
  }

  public function setRare($rare) {
    $this->rare = $rare;
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
