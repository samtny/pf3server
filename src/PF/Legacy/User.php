<?php

namespace PF\Legacy;

class User {
  public $id;
  public $username;
  public $password;
  public $lname;
  public $fname;
  public $uuid;
  public $tokens;
  public $notifications;
  public $banned;
  public function __construct() {
    $this->tokens = array();
    $this->notifications = array();
  }
}
