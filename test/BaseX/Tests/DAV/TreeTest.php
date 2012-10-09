<?php

namespace BaseX\Tests\DAV;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\DAV\Tree;

class TreeTest extends TestCaseDb
{

  function testNodeExists()
  {
    $this->db->add('test/sa.xml', '<root/>');
    $this->db->add('test.xml', '<root/>');
    $this->db->add('test/.protect/sa.xml', '<root/>');
    $tree = new Tree($this->db);
    
    $this->assertTrue($tree->nodeExists('test.xml'));
    $this->assertTrue($tree->nodeExists('test/sa.xml'));
    $this->assertTrue($tree->nodeExists('test'));
    $this->assertFalse($tree->nodeExists(''));
    $this->assertFalse($tree->nodeExists('/'));
    $this->assertFalse($tree->nodeExists('bazinga.txt'));
    
  }
  
  /**
   * @depends testNodeExists
   */
  function testExcluded()
  {
    $tree = new Tree($this->db);
    
    $this->db->add('.protect/test.xml', '<root/>');
    $tree->markDirty(1);
    
    $this->assertEmpty($tree->getChildren(''));
    $this->assertFalse($tree->nodeExists('.protect/test.xml'));
    
    
    
    $this->db->add('sazam/test.xml', '<root/>');
    $this->db->store('sazam/.empty', '');
    $tree->markDirty(1);
    $this->assertFalse($tree->nodeExists('sazam/.empty'));
    
    
    $this->db->store('sazam/.protect/protected', '');
    $tree->markDirty(1);
    
    $this->assertFalse($tree->nodeExists('sazam/.protect/protected'));
    
    $this->assertTrue($tree->nodeExists('sazam'));
    
    $test = $tree->exclude('sazam*');
    
    $this->assertEquals($test, $tree);
    $this->assertFalse($tree->nodeExists('sazam'));
    
  }
  
  function testGetNodeForPath()
  {
    $this->db->add('test/sa.xml', '<root/>');
    $this->db->add('test.xml', '<root/>');
    $tree = new Tree($this->db);
    
    $node = $tree->getNodeForPath('test.xml');
    
    $this->assertInstanceOf('BaseX\DAV\Resource', $node);
    $this->assertInstanceOf('BaseX\Resource\Document', $node->getResource());
    
    $col = $tree->getNodeForPath('test');
    $this->assertInstanceOf('BaseX\DAV\Collection', $col);
  }
  
  function testMove()
  {
    $this->db->add('test.xml', '<root/>');
    
    $tree = new Tree($this->db);
    
    $tree->move('test.xml', 'moved.xml');
    
    $this->assertEquals('moved.xml', $this->session->query("db:list('$this->dbname')")->execute());
    
    $this->assertTrue($tree->nodeExists('moved.xml'));
  }
  
  function testMoveCollection()
  {
    $this->db->add('test/test.xml', '<root/>');
    $tree = new Tree($this->db);
    $tree->move('test', 'sazam');
    
    $this->assertContains('sazam/test.xml', $this->ls());
    $this->assertNotContains('test/test.xml', $this->ls());
    
  }
  
  function testMoveRaw()
  {
    $this->db->store('test.txt', 'test');
    
    $tree = new Tree($this->db);
    
    $tree->move('test.txt', 'moved.txt');
    
    $this->assertEquals('moved.txt', $this->session->query("db:list('$this->dbname')")->execute());
    $this->assertTrue($tree->nodeExists('moved.txt'));
  }
  
  function testCopy()
  {
    $this->db->add('test.xml', '<root/>');
    
    $tree = new Tree($this->db);
    
    $tree->copy('test.xml', 'copy.xml');
    
    $this->assertEquals('copy.xml test.xml', $this->session->query("db:list('$this->dbname')")->execute());
    $this->assertTrue($tree->nodeExists('copy.xml'));
  }
  
  function testCopyCollection()
  {
    $this->db->add('test/test.xml', '<root/>');
    $tree = new Tree($this->db);
    $tree->copy('test', 'sazam');
    
    $this->assertContains('sazam/test.xml', $this->ls());
    $this->assertContains('test/test.xml', $this->ls());
    
  }
}
