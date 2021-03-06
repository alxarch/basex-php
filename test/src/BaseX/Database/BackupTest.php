<?php

namespace BaseX\Database;

use BaseX\Database\Backup;
use BaseX\PHPUnit\TestCaseDb;
use DateTime;

class BackupTest extends TestCaseDb
{
  function testSetData()
  {
    $b = new Backup();
    $data = '<backup size="123">test-2000-01-01-00-12-43.zip</backup>';
    $b->unserialize($data);
    
    $this->assertEquals(123, $b->getSize());
    $this->assertEquals(new DateTime('2000-01-01 00:12:43'), $b->getDate());
    $this->assertEquals('test', $b->getDatabase());
    $this->assertEquals('test-2000-01-01-00-12-43.zip', $b->getFile());
    $this->assertEquals('/some/path/test-2000-01-01-00-12-43.zip', $b->getFilepath('/some/path'));
  }
}
