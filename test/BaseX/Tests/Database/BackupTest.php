<?php

namespace BaseX\Tests;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Database\Backup;



class BackupTest extends TestCaseDb
{
  function testSetData()
  {
    $b = new Backup($this->session);
    
    $data = '<backup size="123">test-2000-01-01-00-12-43.zip</backup>';
    $b->setData($data);
    
    $this->assertEquals(123, $b->getSize());
    $this->assertEquals(date_parse('2000-01-01 00:12:43'), $b->getDate());
    $this->assertEquals('test', $b->getDatabase());
    $this->assertEquals('test-2000-01-01-00-12-43.zip', $b->getFile());
    $dbpath = $this->session->getInfo()->dbpath;
    $this->assertEquals($dbpath.DIRECTORY_SEPARATOR.'test-2000-01-01-00-12-43.zip', $b->getFilepath());
  }
}
