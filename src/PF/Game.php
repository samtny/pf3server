<?php

namespace PF;

use PF\Utilities\StringUtil;

class Game {
  protected $id;
  protected $name;
  protected $name_clean;
  protected $name_dm;
  protected $ipdb;
  protected $abbreviation;
  protected $year;
  protected $manufacturer;
  protected $new;
  protected $rare;
  protected $created;
  protected $updated;

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
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

  public function getIpdb() {
    return $this->ipdb;
  }

  public function setIpdb($ipdb) {
    $this->ipdb = $ipdb;
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

  public function __toString() {
    return $this->name;
  }
}
