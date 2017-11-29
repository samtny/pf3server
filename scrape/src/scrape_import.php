<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_machine_lookup.php';
require_once __DIR__ . '/scrape_venue_lookup.php';
require_once __DIR__ . '/scrape_venue_validate.php';

/**
 * @param $scrape_venue \PF\Venue
 * @param $venue \PF\Venue
 * @return mixed
 */
function scrape_import_merge_to_venue($scrape_venue, &$venue) {
  if (empty($venue->getExternalKey())) {
    $venue->setExternalKey($scrape_venue->getExternalKey());
  }

  if (empty($venue->getUrl()) && !empty($scrape_venue->getUrl())) {
    $venue->setUrl($scrape_venue->getUrl());
  }

  if (empty($venue->getPhone()) && !empty($scrape_venue->getPhone())) {
    $venue->setPhone($scrape_venue->getPhone());
  }

  $venue->setUpdated($scrape_venue->getUpdated());

  return $venue;
}

/**
 * @param $scrape_venue \PF\Venue
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
      echo "Found venue: " . $venue->getId() . "\n";

      if (scrape_venue_validate_fresher($scrape_venue, $venue)) {
        echo "Scrape is fresher" . "\n";

        scrape_import_merge_to_venue($scrape_venue, $venue);

        echo "Updating venue: " . $venue->getId() . "\n";

        if (!$dry_run) {
          $entityManager->persist($venue);

          $entityManager->flush();
        }
      } else {
        echo "Scrape is not fresher" . "\n";

        echo "Declining to update venue: " . $venue->getId() . "\n";
      }
    } else {
      echo "Creating new venue\n";

      if (!$dry_run) {
        $entityManager->persist($scrape_venue);

        $entityManager->flush();
      }
    }
  } else {
    echo "Scrape does not pass validation" . "\n";
  }
}
