<?php

namespace BaseX\Tests\DAV;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\DAV\Resource as DAVResource;
use BaseX\DAV\Tree;

class ResourceTest extends TestCaseDb
{
  function getResource($path='test.xml', $class='BaseX\Resource\Document')
  {
    $this->db->add($path, '<root/>');
    $res = new $class($this->session, $this->dbname, $path);
    $tree = new Tree($this->db);
    return new DAVResource($tree, $res);
  }
  
  function testGetPath()
  {
    $res = $this->getResource('path/to/test.xml');
    $this->assertEquals('path/to', $res->getPath());
    $res = $this->getResource('test.xml');
    $this->assertEmpty($res->getPath());
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
