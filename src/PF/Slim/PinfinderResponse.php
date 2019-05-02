<?php

namespace PF\Slim;

use \Slim\Http\Response;

class PinfinderResponse extends Response {

  private $pinfinderData;

  private $pinfinderMessage;

  /**
   * @return mixed
   */
  public function getPinfinderData() {
    return $this->pinfinderData;
  }

  /**
   * @param mixed $pinfinderData
   */
  public function setPinfinderData($pinfinderData) {
    $this->pinfinderData = $pinfinderData;
  }

  /**
   * @return mixed
   */
  public function getPinfinderMessage() {
    return $this->pinfinderMessage;
  }

  /**
   * @param mixed $pinfinderMessage
   */
  public function setPinfinderMessage($pinfinderMessage) {
    $this->pinfinderMessage = $pinfinderMessage;
  }

}
