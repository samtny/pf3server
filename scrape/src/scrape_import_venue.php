<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_venue_lookup.php';
require_once __DIR__ . '/scrape_venue_validate.php';
require_once __DIR__ . '/scrape_import_games.php';
require_once __DIR__ . '/scrape_import_machines.php';
require_once __DIR__ . '/scrape_prune_machines.php';

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 *
 * @return \PF\Venue
 */
function scrape_import_merge_properties($scrape_venue, $venue) {
  if (empty($venue->getExternalKey())) {
    $venue->setExternalKey($scrape_venue->getExternalKey());
  }

  if (empty($venue->getName())) {
    $venue->setName($scrape_venue->getName());
  }

  if (empty($venue->getStreet())) {
    $venue->setStreet($scrape_venue->getStreet());
  }

  if (empty($venue->getCity())) {
    $venue->setCity($scrape_venue->getCity());
  }

  if (empty($venue->getState())) {
    $venue->setState($scrape_venue->getState());
  }

  if (empty($venue->getZipcode())) {
    $venue->setZipcode($scrape_venue->getZipcode());
  }

  if (empty($venue->getLatitude())) {
    $venue->setLatitude($scrape_venue->getLatitude());
  }

  if (empty($venue->getLongitude())) {
    $venue->setLongitude($scrape_venue->getLongitude());
  }

  if (empty($venue->getUrl()) && !empty($scrape_venue->getUrl())) {
    $venue->setUrl($scrape_venue->getUrl());
  }

  if (empty($venue->getPhone()) && !empty($scrape_venue->getPhone())) {
    $venue->setPhone($scrape_venue->getPhone());
  }

  if (empty($venue->getCreated())) {
    $venue->setCreated($scrape_venue->getCreated());
  }

  $venue->setUpdated($scrape_venue->getUpdated());

  return $venue;
}

/**
 * @param $scrape_venue \PF\Venue
 * @param bool $trust_games
 * @param bool $auto_approve
 * @param bool $dry_run
 */
function scrape_import_venue($scrape_venue, $trust_games, $auto_approve, $dry_run = FALSE) {
  $entityManager = Bootstrap::getEntityManager();

  if (scrape_venue_validate($scrape_venue)) {
    echo "Scrape passes validation" . "\n";

    $venue = scrape_venue_lookup($scrape_venue);

    if (!empty($venue)) {
      echo "Found matching venue: " . $venue->getId() . "\n";
    } else {
      echo "Creating new venue\n";

      $venue = new \PF\Venue(TRUE);
    }

    if (scrape_venue_validate_fresher($scrape_venue, $venue)) {
      $venue = scrape_import_merge_properties($scrape_venue, $venue);

      if ($auto_approve) {
        $venue->approve(TRUE);
      }

      if (!$dry_run) {
        $entityManager->persist($venue);

        $entityManager->flush();
      }

      if ($trust_games) {
        scrape_import_games($scrape_venue, $dry_run);
      }

      scrape_import_machines($scrape_venue, $venue, $dry_run);

      scrape_prune_machines($scrape_venue, $venue, $dry_run);
    } else {
      echo "Declining to merge less fresh venue\n";
    }
  } else {
    echo "Scrape does not pass validation" . "\n";
  }
}
