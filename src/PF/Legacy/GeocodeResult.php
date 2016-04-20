<?php

namespace PF\Legacy;

class GeocodeResult {
  public $coordinate;
  public $southwest;
  public $northeast;
  function __construct() {
    $this->coordinate = new LatLon();
    $this->southwest = new LatLon();
    $this->northeast = new LatLon();
  }
}
