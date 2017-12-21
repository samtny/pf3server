<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/scrape_venue_lookup.php';
require_once __DIR__ . '/scrape_venue_validate.php';
require_once __DIR__ . '/scrape_import_games.php';
require_once __DIR__ . '/scrape_import_machines.php';
require_once __DIR__ . '/scrape_prune_machines.php';
require_once __DIR__ . '/scrape_tidy_venue.php';

use \PF\Venue;

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
 * @param $venue \PF\Venue
 * @param bool $trust_games
 * @param bool $tidy
 * @param bool $dry_run
 * @return bool
 */
function scrape_import_update_venue($scrape_venue, $venue, $trust_games = FALSE, $tidy = FALSE, $dry_run = FALSE) {
  $imported = FALSE;

  $entityManager = Bootstrap::getEntityManager();
  $logger = Bootstrap::getLogger('pf3_scrape');

  if (scrape_venue_validate_fresher($scrape_venue, $venue)) {
    $logger->info("Updating existing venue", array('name' => $venue->getName()));

    if ($tidy) {
      $scrape_venue = scrape_tidy_venue($scrape_venue);
    }

    $venue = scrape_import_merge_properties($scrape_venue, $venue);

    if (!$dry_run) {
      $entityManager->persist($venue);

      $entityManager->flush();
    }

    if ($trust_games) {
      scrape_import_games($scrape_venue, $dry_run);
    }

    scrape_import_machines($scrape_venue, $venue, $dry_run);

    scrape_prune_machines($scrape_venue, $venue, $dry_run);

    $imported = TRUE;
  } else {
    $logger->debug("Declining to merge less fresh venue", array('name' => $scrape_venue->getName()));
  }

  return $imported;
}

/**
 * @param $scrape_venue \PF\Venue
 * @param bool $trust_games
 * @param bool $auto_approve
 * @param bool $soft_approve
 * @param bool $tidy
 * @param bool $dry_run
 * @return bool
 */
function scrape_import_new_venue($scrape_venue, $trust_games = FALSE, $auto_approve = FALSE, $soft_approve = FALSE, $tidy = FALSE, $dry_run = FALSE) {
  $imported = FALSE;

  $entityManager = Bootstrap::getEntityManager();
  $logger = Bootstrap::getLogger('pf3_scrape');

  if ($tidy) {
    $scrape_venue = scrape_tidy_venue($scrape_venue);
  }

  $venue = scrape_import_merge_properties($scrape_venue, new Venue(TRUE));

  if ($auto_approve) {
    $venue->approve(TRUE);
  }
  else if ($soft_approve && scrape_venue_validate_complete($venue)) {
    $venue->approve(TRUE);
  }

  if (scrape_venue_validate_no_conflict($venue)) {
    $logger->info("Importing new venue", array('name' => $venue->getName()));

    if (!$dry_run) {
      $entityManager->persist($venue);

      $entityManager->flush();
    }

    if ($trust_games) {
      scrape_import_games($scrape_venue, $dry_run);
    }

    scrape_import_machines($scrape_venue, $venue, $dry_run);

    $imported = TRUE;
  }
  else {
    $logger->warning("Declining to import conflicting venue", array('name' => $venue->getName()));
  }

  return $imported;
}

/**
 * @param $scrape_venue \PF\Venue
 * @param bool $trust_games
 * @param bool $auto_approve
 * @param bool $soft_approve
 * @param bool $tidy
 * @param bool $dry_run
 * @return bool
 */
function scrape_import_venue($scrape_venue, $trust_games = FALSE, $auto_approve = FALSE, $soft_approve = FALSE, $tidy = FALSE, $dry_run = FALSE) {
  $imported = FALSE;

  $logger = Bootstrap::getLogger('pf3_scrape');

  if (scrape_venue_validate_is_fresh($scrape_venue)) {
    $logger->debug("Venue is fresh");

    $venue = scrape_venue_lookup($scrape_venue);

    if (!empty($venue)) {
      $logger->debug("Found matching venue: " . $venue->getId());

      $imported = scrape_import_update_venue($scrape_venue, $venue, $trust_games, $tidy, $dry_run);
    } else {
      if (scrape_venue_validate_has_games($scrape_venue)) {
        $logger->debug("Did not find matching venue");

        $imported = scrape_import_new_venue($scrape_venue, $trust_games, $auto_approve, $soft_approve, $tidy, $dry_run);
      } else {
        $logger->info("Declining to create new venue with zero games", array('scrape_venue', $scrape_venue));
      }
    }
  } else {
    $logger->info("Declining to process stale venue", array('scrape_venue' => $scrape_venue));
  }

  return $imported;
}
