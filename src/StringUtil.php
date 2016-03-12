<?php

namespace PF;

use DoubleMetaPhone;

class StringUtil {
  public static function cleanName($name) {
    $clean = $name;

    // special case of 's;
    $clean = preg_replace("/'s\s/i", "s ", $clean);

    // kill apostrophe'd single letters;
    $clean = preg_replace("/'[a-zA-Z0-9]\s/", " ", $clean);

    // kill apostrophes in general;
    $clean = preg_replace("/'/", "", $clean);

    // replace non-alphanumeric with space
    $clean = preg_replace("/[^a-zA-Z0-9\s]/", " ", $clean);

    // remove double-spacing
    $clean = preg_replace("/\s+/", " ", $clean);

    // remove leading "the"
    $clean = preg_replace("/^the/i", "", $clean);

    // normalize numerics one thru ten, eleven
    $clean = preg_replace("/1st/i", "First", $clean);
    $clean = preg_replace("/2nd/i", "Second", $clean);
    $clean = preg_replace("/3rd/i", "Third", $clean);
    $clean = preg_replace("/4th/i", "Fourth", $clean);
    $clean = preg_replace("/5th/i", "Fifth", $clean);
    $clean = preg_replace("/6th/i", "Sixth", $clean);
    $clean = preg_replace("/7th/i", "Seventh", $clean);
    $clean = preg_replace("/8th/i", "Eighth", $clean);
    $clean = preg_replace("/9th/i", "Ninth", $clean);
    $clean = preg_replace("/10th/i", "Tenth", $clean);
    $clean = preg_replace("/11th/i", "Eleventh", $clean);

    // trim
    $clean = trim($clean);

    return $clean;
  }

  public static function dmName($name) {
    $dm_parts = array();

    $name_clean = self::cleanName($name);

    $name_clean_parts = explode(" ", $name_clean);

    foreach ($name_clean_parts as $name_clean_part) {
      $name_clean_part_dm = new DoubleMetaPhone($name_clean_part);

      if (!empty($name_clean_part_dm->secondary) && $name_clean_part_dm->primary != $name_clean_part_dm->secondary) {
        $dm_parts[] = $name_clean_part_dm->primary . ':' . $name_clean_part_dm->secondary;
      } else {
        $dm_parts[] = $name_clean_part_dm->primary;
      }
    }

    return implode(" ", $dm_parts);
  }
}