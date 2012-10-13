<?php

namespace BaseX\DAV;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\DAV\CollectionNode;
use BaseX\DAV\ObjectTree;
use BaseX\StreamWrapper;

class DAVCollectionNodeTest extends TestCaseDb
{
  public function setUp()
  {
    parent::setUp();
    StreamWrapper::register($this->session);
    $this->db->add('test.xml', '<root/>');
    $this->db->add('test1.xml', '<root/>');
    $this->db->add('test2.xml', '<root/>');
    $this->db->add('dir/test3.xml', '<root/>');
    $this->db->add('dir/test4.xml', '<root/>');
    $this->db->add('dir/dada/test4.xml', '<root/>');
    $this->db->add('dir/dada/test5.xml', '<root/>');
    
    $this->tree = new ObjectTree($this->db);
  }

  public function testGetChildren()
  {
    
    $col = new CollectionNode($this->tree, '');
    
    $children = $col->getChildren();
    $this->assertEquals(4, count($children));
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $children[0]);
    $this->assertEquals('test.xml', $children[0]->getName());
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $children[1]);
    $this->assertEquals('test2.xml', $children[2]->getName());
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $children[2]);
    $this->assertEquals('test1.xml', $children[1]->getName());
    $this->assertInstanceOf('BaseX\DAV\CollectionNode', $children[3]);
    $this->assertEquals('dir', $children[3]->getName());
    
    $dir = $children[3];
    $children = $dir->getChildren();
    $this->assertEquals(3, count($children));
    $this->assertInstanceOf('BaseX\DAV\CollectionNode', $children[2]);
    $this->assertEquals('dada', $children[2]->getName());
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $children[0]);
    $this->assertEquals('test3.xml', $children[0]->getName());
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $children[1]);
    $this->assertEquals('test4.xml', $children[1]->getName());
    
    $dada = $children[2];
    $children = $dada->getChildren();
    $this->assertEquals(2, count($children));
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $children[0]);
    $this->assertEquals('test4.xml', $children[0]->getName());
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $children[1]);
    $this->assertEquals('test5.xml', $children[1]->getName());
    
  }
  
  public function testGetChild()
  {
    $col = new CollectionNode($this->tree, '');
    
    $result = $col->getChild('test1.xml');
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $result);
    $this->assertEquals('test1.xml', $result->getName());
    
    $col = $col->getChild('dir');
    
    $this->assertInstanceOf('BaseX\DAV\CollectionNode', $col);
    $this->assertEquals('dir', $col->getName());
    
    $result = $col->getChild('test3.xml');
    
    $this->assertInstanceOf('BaseX\DAV\ResourceNode', $result);
    $this->assertEquals('test3.xml', $result->getName());
    
    $col = $col->getChild('dada');
    $this->assertInstanceOf('BaseX\DAV\CollectionNode', $col);
    $this->assertEquals('dada', $col->getName());
    
    $result = $col->getChild('test4.xml');
    $this->assertInstanceOf('BaseX\DAV\CollectionNode', $col);
    $this->assertEquals('dada', $col->getName());
  }
  
  public function testChildExists()
  {
    $col = new CollectionNode($this->tree, '');
    
    $this->assertTrue($col->childExists('test1.xml'));
    
    $this->assertTrue($col->childExists('dir'));
    $this->assertFalse($col->childExists('nothere.png'));
    $this->assertFalse($col->childExists('test3.xml'));
    $this->assertTrue($col->childExists('dir/test3.xml'));
    
    $col = $col->getChild('dir');
    $this->assertTrue($col->childExists('dada'));
    $this->assertFalse($col->childExists('nothere.png'));
    $this->assertTrue($col->childExists('test3.xml'));
    $this->assertFalse($col->childExists('test1.xml'));
    
    $col = $col->getChild('dada');
    $this->assertTrue($col->childExists('test4.xml'));
    $this->assertTrue($col->childExists('test5.xml'));
    $this->assertFalse($col->childExists('nothere.png'));
  }
  
  
  public function testCreateFile()
  {
    
    $col = new CollectionNode($this->tree, '');
    
    $etag = $col->createFile('dir/test.xml', '<test/>');
    
    $this->assertTrue($this->db->exists('dir/test.xml'));
    
    $this->assertNotNull($etag);
    
    $etag = $col->createFile('dir/test.txt', 'test');
    
    $this->assertNotNull($etag);
    
    $this->assertTrue($this->db->exists('dir/test.txt'));
    $xql = "db:list-details('$this->dbname', 'dir/test.txt')/@size/string()";
    
    $this->assertFalse('0' === $this->session->query($xql)->execute());
  }
  
  public function tearDown() {
    StreamWrapper::unregister();
    parent::tearDown();
  }
}
