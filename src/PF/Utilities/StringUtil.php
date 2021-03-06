<?php

namespace PF\Utilities;

class StringUtil {
  public static function cleanName($name) {
    $clean = $name;

    // special case of 's;
    $clean = preg_replace("/'s\s/i", "s ", $clean);

    // text between parentheses, except game qualifiers;
    $clean = preg_replace("/\((?!(Pro|LE|Premium|Super LE|Premium\/LE)).+\)/i", "", $clean);

    // remaining parentheses:
    $clean = preg_replace("/\(|\)/", "", $clean);

    // remove leading "the "
    $clean = preg_replace("/^the\s/i", "", $clean);

    // remove trailing " bar & grill"
    $clean = preg_replace("/\sbar\s((\&|and)\s)?grill$/i", "", $clean);

    // remove trailing " bar & restaurant"
    $clean = preg_replace("/\sbar\s((\&|and)\s)?restaurant$/i", "", $clean);

    // remove trailing " bar"
    $clean = preg_replace("/\sbar$/i", "", $clean);

    // remove trailing " lounge"
    $clean = preg_replace("/\slounge$/i", "", $clean);

    // &amp
    $clean = preg_replace("/&amp/", "", $clean);

    // kill apostrophe'd single letters;
    $clean = preg_replace("/'[a-zA-Z0-9]\s/", " ", $clean);

    // kill apostrophes in general;
    $clean = preg_replace("/'/", "", $clean);

    // misc
    $clean = preg_replace("/\sand\s|\sor\s|\sof\s|\sfrom\s/i", " ", $clean);

    // replace non-alphanumeric with space
    $clean = preg_replace("/[^a-zA-Z0-9\s]/", " ", $clean);

    // remove double-spacing
    $clean = preg_replace("/\s+/", " ", $clean);

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
      $dm = new DoubleMetaPhone();

      $dm->DoubleMetaPhone($name_clean_part);

      if (!empty($dm->primary)) {
        if (!empty($dm->secondary) && $dm->primary != $dm->secondary) {
          $dm_parts[] = $dm->primary . ':' . $dm->secondary;
        } else {
          $dm_parts[] = $dm->primary;
        }
      }
    }

    return implode(" ", $dm_parts);
  }

  public static function abbrName($name) {
    $abbr = '';

    $name_clean = self::cleanName($name);

    $words = explode(' ', $name_clean);

    foreach ($words as $word) {
      $abbr .= $word[0];
    }

    return $abbr;
  }

  public static function namesAreSimilar($nameA, $nameB, $threshold = 80) {
    $similar = FALSE;

    $dmA = self::dmName($nameA);
    $dmB = self::dmName($nameB);

    similar_text($dmA, $dmB, $percent);

    if ($percent > $threshold) {
      $similar = TRUE;
    }

    return $similar;
  }
}
