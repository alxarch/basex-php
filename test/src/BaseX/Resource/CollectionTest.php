<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Resource;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Collection;
use BaseX\Resource\Tree;

/**
 * Description of CollectionTest
 *
 * @author alxarch
 */
class CollectionTest extends TestCaseDb
{
  function testConstruct()
  {
    $col = new Collection($this->db, null);
  }
  
  function testGetTree() 
  {
    $col = new Collection($this->db, '');
    $this->assertTrue($col->getTree() instanceof Tree);
  }
  
  function testGetChildren()
  {
    $this->db->add('test.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    $this->db->add('test/path/test.xml', '<test/>');
    
    $col = new Collection($this->db, '');
    $mapper = new \BaseX\Resource\ResourceMapper($this->db);
    $this->assertTrue($mapper instanceof \BaseX\Query\Result\SimpleXMLMapperInterface);
    $contents = $col->getChildren($mapper);
    $this->assertTrue(is_array($contents));
    $this->assertEquals(2, count($contents));
    $this->assertInstanceOf('BaseX\Resource\Collection', $contents[1]);
    $this->assertInstanceOf('BaseX\Resource\Document', $contents[0]);
    
    $this->db->add('test2.xml', '<test/>');
    $this->db->add('test/test3.xml', '<test/>');
    
    $col->refresh();
    $contents = $col->getChildren($mapper);
    $this->assertEquals(3, count($contents));
    
    $test = $contents[2]->getChildren($mapper);
    $this->assertEquals(3, count($test));
    
    $this->assertEquals(1, count($test[2]->getChildren($mapper)));
    $this->assertEquals('test/path', $test[2]->getPath());
  }
  
  public function testGetChildrenEmpty()
  {
    $col = new Collection($this->db, null);
    $this->assertEmpty($col->getChildren());
  }
 
}
