<?php

namespace PF\Utilities\Tests;

use PF\Utilities\StringUtil;

class StringUtilTest extends \PHPUnit_Framework_TestCase
{
  public function testCleanName() {
    $name = "jack bar";

    $clean_name = StringUtil::cleanName($name);

    $this->assertEquals('jack bar', $clean_name);
  }

  public function testDmName() {
    $name = "jack bar";

    $dm_name = StringUtil::dmName($name);

    $this->assertEquals('JK:AK PR', $dm_name);
  }
}
