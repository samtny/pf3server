<?php

namespace PF;

class Machine {
  protected $id;
  protected $ipdb;
  protected $game;
  protected $venue;
  protected $condition;
  protected $price;
  protected $deleted;
  protected $created;
  protected $updated;

  public function getIpdb() {
    return !empty($this->game) ? $this->game->getId() : null;
  }

  public function getName() {
    return !empty($this->game) ? $this->game->getName() : null;
  }

  public function getNew() {
    return !empty($this->game) ? $this->game->getNew() : null;
  }

  public function getRare() {
    return !empty($this->game) ? $this->game->getRare() : null;
  }

  public function postDeserialize() {
    $game = new Game();
    $game->setId($this->ipdb);

    $this->setGame($game);
  }

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
    $this->deleted = false;
  }

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function setIpdb($ipdb) {
    $this->ipdb = $ipdb;
  }

  public function getGame() {
    return $this->game;
  }

  public function setGame($game) {
    $this->game = $game;
  }

  public function getVenue()
  {
    return $this->venue;
  }

  public function setVenue($venue) {
    $this->venue = $venue;
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

  public function getCondition() {
    return $this->condition;
  }

  public function setCondition($condition) {
    $this->condition = $condition;
  }

  public function getPrice() {
    return $this->price;
  }

  public function setPrice($price) {
    $this->price = $price;
  }
}
