<?php

namespace PF\Utilities;

use Bootstrap;

class GameUtil {
  /**
   * @param $game \PF\Game
   * @return string
   */
  public static function generateAbbreviation($game) {
    $abbreviation = StringUtil::abbrName($game->getName());

    $bare_abbreviation = $abbreviation;

    $tries = 0;

    while (self::checkAbbreviationExists($abbreviation) && $tries < 1000) {
      $abbreviation = $bare_abbreviation . $tries;

      $tries++;
    }

    return $abbreviation;
  }

  public static function checkAbbreviationExists($abbreviation) {
    $entityManager = Bootstrap::getEntityManager();

    return $entityManager->getRepository('\PF\Game')->findOneBy(array('abbreviation' => $abbreviation));
  }
}
