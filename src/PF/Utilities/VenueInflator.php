<?php

namespace PF\Utilities;

use Bootstrap;
use Doctrine;

class VenueInflator {
  private $entityManager;

  public function __construct() {
    $this->entityManager = Bootstrap::getEntityManager();
  }

  public function inflate(&$venues) {
    $this->inflateGames($venues);

    //$this->inflateComments($venues);
  }

  public function inflateGames(&$venues) {
    $map = array();
    $venueIds = array();

    foreach ($venues as $index => $venue) {
      $map[$venue->getId()] = $index;

      $venueIds[] = $venue->getId();
    }

    $machines = $this->entityManager->getRepository('\PF\Machine')->getMachinesForVenueIds($venueIds);

    foreach ($machines as $machine) {
      $index = $map[$machine->getVenue()->getId()];

      $venues[$index]->addMachine($machine);
    }
  }
}
