<?php

namespace PF\Legacy;

class Venue {
  public $id;
  public $name;
  public $street;
  public $city;
  public $state;
  public $zipcode;
  public $neighborhood;
  public $country;
  public $lat;
  public $lon;
  public $phone;
  public $source;
  public $sourceid;
  public $url;
  public $dist;
  public $created;
  public $updated;
  public $flag;
  public $fsqid;
  public $games;
  public $comments;
  public $images;
  public $leagues;
  public $tournaments;
  function __construct() {
    $this->games = array();
    $this->comments = array();
    $this->images = array();
    $this->leagues = array();
    $this->tournaments = array();
  }
  public function addGame($game) {
    $this->games[] = $game;
  }
  public function addComment($comment) {
    $this->comments[] = $comment;
  }
  public function addImage($image) {
    $this->images[] = $image;
  }
  public function addLeague($league) {
    $this->leagues[] = $league;
  }
  public function addTournament($tournament) {
    $this->tournaments[] = $tournament;
  }
  function __sleep() {
    $keys = array();
    foreach (get_object_vars($this) as $key => $var) {
      if ($var != null) {
        $keys[] = $key;
      }
    }
    return $keys;
  }
}
