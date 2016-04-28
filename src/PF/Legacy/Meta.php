<?php

namespace PF\Legacy;

class Meta {
  public $q;
  public $n;
  public $gamedict;
  public $stats;
  public $message;
  function __construct() {
    $this->gamedict = new GameDict();
  }
}
