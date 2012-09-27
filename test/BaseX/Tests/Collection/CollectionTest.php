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
  
  function testListContents()
  {
    $this->db->add('test.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    
    $col = new Collection($this->session, $this->dbname, '');
    
    $contents = $col->listContents();
    $this->assertTrue(is_array($contents));
    $this->assertEquals(2, count($contents));
    $this->assertInstanceOf('BaseX\Collection', $contents[0]);
    $this->assertInstanceOf('BaseX\Resource\Document', $contents[1]);
    
    $this->db->add('test2.xml', '<test/>');
    $this->db->add('test/test3.xml', '<test/>');
    
    $col->reloadInfo();
    $contents = $col->listContents();
    $this->assertTrue(is_array($contents));
    $this->assertEquals(3, count($contents));
  }
  
}
