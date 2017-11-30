<?php

namespace PF;

class Machine {
  protected $id;
  protected $game;
  protected $venue;
  protected $condition;
  protected $price;
  protected $status;
  protected $created;
  protected $updated;
  protected $external_key;

  public function getIpdb() {
    return !empty($this->game) ? $this->game->getIpdb() : null;
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

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
    $this->status = 'ACTIVE';
  }

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function setStatus($status)
  {
    $this->status = $status;
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

  public function getExternalKey() {
    return $this->external_key;
  }

  public function setExternalKey($external_key) {
    $this->external_key = $external_key;
  }

  public function touch() {
    $this->updated = new \DateTime("now");
  }

  public function delete() {
    $this->status = 'DELETED';
  }

  public function activate() {
    $this->status = 'ACTIVE';
  }
}
