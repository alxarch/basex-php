<?php

namespace BaseX\Dav;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Dav\ObjectTree;

class ObjectTreeTest extends TestCaseDb {

  function testNodeExists() {
    $this->db->add('test/sa.xml', '<root/>');
    $this->db->add('test.xml', '<root/>');
    $this->db->add('test/.protect/sa.xml', '<root/>');
    $tree = new ObjectTree($this->db);

    $this->assertTrue($tree->nodeExists('test.xml'));
    $this->assertTrue($tree->nodeExists('test'));
    $this->assertFalse($tree->nodeExists(''));
    $this->assertF/alse($tree->nodeExists('/'));
    $this->assertFalse($tree->nodeExists('bazinga.txt'));
    $this->assertTrue($tree->nodeExists('test/.protect/sa.xml'));
    $this->assertTrue($tree->nodeExists('test/sa.xml'));
  }
  
  function testGetChildren()
  {
    $this->db->add('test/sa.xml', '<root/>');
    $this->db->add('test.xml', '<root/>');
    $this->db->add('test/.protect/sa.xml', '<root/>');
    $tree = new ObjectTree($this->db);
    $children = $tree->getChildren('');
    $this->assertTrue(is_array($children));
    $this->assertEquals(2, count($children));
    
    $granchildren = $tree->getChildren('test');
    
    $this->assertTrue(is_array($granchildren));
    $this->assertEquals(2, count($granchildren));
  }

  function testGetNodeForPath() {
    $this->db->add('test/sa.xml', '<root/>');
    $this->db->add('test.xml', '<root/>');
    $tree = new ObjectTree($this->db);

    $node = $tree->getNodeForPath('test.xml');

    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $node);

    $col = $tree->getNodeForPath('test');
    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $col);
  }

  function testMove() {
    $this->db->add('test.xml', '<root/>');

    $tree = new ObjectTree($this->db);

    $tree->move('test.xml', 'moved.xml');

    $this->assertEquals('moved.xml', $this->session->query("db:list('$this->dbname')")->execute());

    $this->assertTrue($tree->nodeExists('moved.xml'));
  }

  function testMoveCollection() {
    $this->db->add('test/test.xml', '<root/>');
    $tree = new ObjectTree($this->db);
    $tree->move('test', 'sazam');

    $this->assertContains('sazam/test.xml', $this->ls());
    $this->assertNotContains('test/test.xml', $this->ls());
  }

  function testMoveRaw() {
    $this->db->store('test.txt', 'test');

    $tree = new ObjectTree($this->db);

    $tree->move('test.txt', 'moved.txt');

    $this->assertEquals('moved.txt', $this->session->query("db:list('$this->dbname')")->execute());
    $this->assertTrue($tree->nodeExists('moved.txt'));
  }

  function testCopy() {
    $this->db->add('test.xml', '<root/>');

    $tree = new ObjectTree($this->db);

    $tree->copy('test.xml', 'copy.xml');

    $this->assertEquals('copy.xml test.xml', $this->session->query("db:list('$this->dbname')")->execute());
    $this->assertTrue($tree->nodeExists('copy.xml'));
  }

  function testCopyCollection() {
    $this->db->add('test/test.xml', '<root/>');
    $tree = new ObjectTree($this->db);
    $tree->copy('test', 'sazam');

    $this->assertContains('sazam/test.xml', $this->ls());
    $this->assertContains('test/test.xml', $this->ls());
  }

}
