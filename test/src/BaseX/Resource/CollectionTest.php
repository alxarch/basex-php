<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Resource;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Collection;

/**
 * Description of CollectionTest
 *
 * @author alxarch
 */
class CollectionTest extends TestCaseDb
{
  
  function setUp() {
    parent::setUp();
    $this->collection = new Collection($this->db, null);
  }
  
  function testGetChildren()
  {
    $this->db->add('test.xml', '<test/>');
    $this->db->add('as.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    $this->db->add('test/path/test.xml', '<test/>');
    
    $contents = $this->collection->getChildren();
    $this->assertTrue(is_array($contents));
    $this->assertEquals(3, count($contents));
    $this->assertInstanceOf('BaseX\Resource\Document', $contents['test.xml']);
    $this->assertInstanceOf('BaseX\Resource\Collection', $contents['test']);
    
    $this->db->add('test2.xml', '<test/>');
    $this->db->add('test/test3.xml', '<test/>');
    
    $this->collection->refresh();
    $contents = $this->collection->getChildren();
    $this->assertEquals(4, count($contents));
    
    $test = $contents['test']->getChildren();
    $this->assertEquals(3, count($test));
    $this->assertEquals(1, count($test['path']->getChildren()));
    $this->assertEquals('test/path', $test['path']->getPath());
  }
  
  public function testGetChildrenEmpty()
  {
    $col = new Collection($this->db, null);
    $this->assertEmpty($col->getChildren());
  }
  
  public function testGetChild()
  {
    $this->assertNull();
  }
 
}
