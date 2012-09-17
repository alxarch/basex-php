<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Tests\Collection;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Collection;
/**
 * Description of CollectionTest
 *
 * @author alxarch
 */
class CollectionTest extends TestCaseDb
{
  function testConstruct()
  {
    $col = new Collection($this->session, $this->dbname, null);
    
  }
  
  function testGetPath()
  {
    $col = new Collection($this->session, $this->dbname, null);
    
    $this->assertEquals('', $col->getPath());
  }
  
  function testListContentsEmpty()
  {
    $col = new Collection($this->session, $this->dbname, null);
    $this->assertEmpty($col->listContents());
  }
  
}
