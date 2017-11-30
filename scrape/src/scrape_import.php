<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_venue_lookup.php';
require_once __DIR__ . '/scrape_venue_validate.php';
require_once __DIR__ . '/scrape_import_machines.php';
require_once __DIR__ . '/scrape_prune_machines.php';

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 * @return mixed
 */
function scrape_import_merge_to_venue($scrape_venue, $venue) {
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
 * @param bool $dry_run
 */
function scrape_import($scrape_venue, $dry_run = FALSE) {
  $entityManager = Bootstrap::getEntityManager();

  if (scrape_venue_validate($scrape_venue)) {
    echo "Scrape passes validation" . "\n";

    echo "Looking up venue by external key: " . $scrape_venue->getExternalKey() . "\n";
    $venue = $entityManager->getRepository('\PF\Venue')->findOneBy(array('external_key' => $scrape_venue->getExternalKey()));

    if (empty($venue)) {
      echo "Looking up venue by fuzzy" . "\n";

      $venue = scrape_venue_fuzzy_lookup($entityManager, $scrape_venue);
    }

    if (!empty($venue)) {
      echo "Found matching venue: " . $venue->getId() . "\n";

      if (scrape_venue_validate_fresher($scrape_venue, $venue)) {
        echo "Scrape is fresher" . "\n";

        echo "Updating venue: " . $venue->getId() . "\n";

        $venue = scrape_import_merge_to_venue($scrape_venue, $venue);

        if (!$dry_run) {
          $entityManager->persist($venue);

          $entityManager->flush();
        }

        scrape_import_machines($scrape_venue, $venue, $dry_run);
        scrape_prune_machines($scrape_venue, $venue, $dry_run);
      } else {
        echo "Scrape is not fresher" . "\n";

        echo "Declining to update venue: " . $venue->getId() . "\n";
      }
    } else {
      echo "Did not find matching venue\n";

      $venue = new \PF\Venue(TRUE);

      $venue = scrape_import_merge_to_venue($scrape_venue, $venue);

      if (!$dry_run) {
        $entityManager->persist($venue);

        $entityManager->flush();
      }

      scrape_import_machines($scrape_venue, $venue, $dry_run);
    }
  } else {
    echo "Scrape does not pass validation" . "\n";
  }
}
