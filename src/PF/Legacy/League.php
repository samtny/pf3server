<?php

namespace PF\Legacy;

class League {
  public $id;
  public $name;
  public $teams;
  public function __construct() {
    $this->teams = array();
  }
  public function addTeam($team) {
    $this->teams[] = $team;
  }
}
