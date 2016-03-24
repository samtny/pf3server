<?php

namespace PF;

class Comment {
  protected $id;
  protected $venue;
  protected $text;
  protected $status;
  protected $created;
  protected $updated;

  public function __construct() {
    $this->created = new \DateTime("now");
    $this->updated = new \DateTime("now");
    $this->status = 'NEW';
  }

  public function getId()
  {
    return $this->id;
  }

  public function getVenue() {
    return $this->venue;
  }

  public function setVenue($venue) {
    $this->venue = $venue;
  }

  public function getText() {
    return $this->text;
  }

  public function setText($text) {
    $this->text = $text;
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

  public function approve() {
    $this->status = 'APPROVED';
  }

  public function delete() {
    $this->status = 'DELETED';
  }
}
