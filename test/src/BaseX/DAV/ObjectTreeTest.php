<?php

namespace BaseX\DAV;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\DAV\ObjectTree;

class DAVObjectTreeTest extends TestCaseDb
{

  function testNodeExists()
  {
    $this->db->add('test/sa.xml', '<root/>');
    $this->db->add('test.xml', '<root/>');
    $this->db->add('test/.protect/sa.xml', '<root/>');
    $tree = new ObjectTree($this->db);
    
    $this->assertTrue($tree->nodeExists('test.xml'));
    $this->assertTrue($tree->nodeExists('test/sa.xml'));
    $this->assertTrue($tree->nodeExists('test'));
    $this->assertFalse($tree->nodeExists(''));
    $this->assertFalse($tree->nodeExists('/'));
    $this->assertFalse($tree->nodeExists('bazinga.txt'));
    
  }
  
  function testGetNodeForPath()
  {
    $this->db->add('test/sa.xml', '<root/>');
    $this->db->add('test.xml', '<root/>');
    $tree = new ObjectTree($this->db);
    
    $node = $tree->getNodeForPath('test.xml');
    
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $node);
    
    $col = $tree->getNodeForPath('test');
    $this->assertInstanceOf('BaseX\DAV\CollectionNode', $col);
  }
  
  function testMove()
  {
    $this->db->add('test.xml', '<root/>');
    
    $tree = new ObjectTree($this->db);
    
    $tree->move('test.xml', 'moved.xml');
    
    $this->assertEquals('moved.xml', $this->session->query("db:list('$this->dbname')")->execute());
    
    $this->assertTrue($tree->nodeExists('moved.xml'));
  }
  
  function testMoveCollection()
  {
    $this->db->add('test/test.xml', '<root/>');
    $tree = new ObjectTree($this->db);
    $tree->move('test', 'sazam');
    
    $this->assertContains('sazam/test.xml', $this->ls());
    $this->assertNotContains('test/test.xml', $this->ls());
    
  }
  
  function testMoveRaw()
  {
    $this->db->store('test.txt', 'test');
    
    $tree = new ObjectTree($this->db);
    
    $tree->move('test.txt', 'moved.txt');
    
    $this->assertEquals('moved.txt', $this->session->query("db:list('$this->dbname')")->execute());
    $this->assertTrue($tree->nodeExists('moved.txt'));
  }
  
  function testCopy()
  {
    $this->db->add('test.xml', '<root/>');
    
    $tree = new ObjectTree($this->db);
    
    $tree->copy('test.xml', 'copy.xml');
    
    $this->assertEquals('copy.xml test.xml', $this->session->query("db:list('$this->dbname')")->execute());
    $this->assertTrue($tree->nodeExists('copy.xml'));
  }
  
  function testCopyCollection()
  {
    $this->db->add('test/test.xml', '<root/>');
    $tree = new ObjectTree($this->db);
    $tree->copy('test', 'sazam');
    
    $this->assertContains('sazam/test.xml', $this->ls());
    $this->assertContains('test/test.xml', $this->ls());
    
  }
}
