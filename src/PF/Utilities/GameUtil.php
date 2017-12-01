<?php

namespace PF\Utilities;

use Bootstrap;

class GameUtil {
  /**
   * @param $game \PF\Game
   * @return string
   */
  public static function generateAbbreviation($game) {
    $abbreviation = null;

    $bare_abbreviation = StringUtil::abbrName($game->getName());

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
