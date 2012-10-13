<?php

namespace BaseX\DAV;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\DAV\ResourceNode;
use BaseX\DAV\ObjectTree;

class DAVResourceNodeTest extends TestCaseDb
{
  function getResource($path='test.xml')
  {
    $this->db->add($path, '<root/>');
    $tree = new ObjectTree($this->db);
    return new ResourceNode($tree, $path);
  }
  
  function testGetName()
  {
    $res = $this->getResource('path/to/test.xml');
    $this->assertEquals('test.xml', $res->getName());
    $res = $this->getResource('test.xml');
    $this->assertEquals('test.xml', $res->getName());
  }
  
  function testDelete()
  {
    $res = $this->getResource('path/to/test.xml');
    $res->delete();
    
    $this->assertNotContains('path/to/test.xml', $this->ls());
  }
  
  function testSetName() 
  {
    $res = $this->getResource();
    
    $res->setName('renamed.xml');
    
    $list = $this->ls();
    $this->assertContains($list, 'renamed.xml');
    $this->assertNotContains($list, 'test.xml');
    
  }
}
