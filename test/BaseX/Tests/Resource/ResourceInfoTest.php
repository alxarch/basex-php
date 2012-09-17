<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Tests\Resource;

use BaseX\Resource\ResourceInfo;

use BaseX\PHPUnit\TestCaseDb;

/**
 * Description of ResourceInfoTest
 *
 * @author alxarch
 */
class ResourceInfoTest extends TestCaseDb
{
  function testGet()
  {
    $this->assertEmpty(ResourceInfo::get($this->session, 'nothere', 'dafsf'));
  }
}

