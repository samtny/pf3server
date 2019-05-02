<?php

namespace PF\Utilities\Tests;

use PF\Utilities\StringUtil;

class StringUtilTest extends \PHPUnit\Framework\TestCase
{
  public function testCleanName() {
    $name = "jack bar";

    $clean_name = StringUtil::cleanName($name);

    $this->assertEquals('jack', $clean_name);
  }

  public function testDmName() {
    $name = "jack bar";

    $dm_name = StringUtil::dmName($name);

    $this->assertEquals('JK:AK', $dm_name);
  }
}
