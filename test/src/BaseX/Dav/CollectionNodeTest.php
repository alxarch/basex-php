<?php

namespace BaseX\Dav;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Dav\CollectionNode;
use BaseX\Dav\ObjectTree;
use BaseX\StreamWrapper;

class CollectionNodeTest extends TestCaseDb
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
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $children['test.xml']);
    $this->assertEquals('test.xml', $children['test.xml']->getName());
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $children['test2.xml']);
    $this->assertEquals('test2.xml', $children['test2.xml']->getName());
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $children['test1.xml']);
    $this->assertEquals('test1.xml', $children['test1.xml']->getName());
    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $children['dir']);
    $this->assertEquals('dir', $children['dir']->getName());
    
    $dir = $children['dir'];
    $children = $dir->getChildren();
    $this->assertEquals(3, count($children));
    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $children['dada']);
    $this->assertEquals('dada', $children['dada']->getName());
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $children['test3.xml']);
    $this->assertEquals('test3.xml', $children['test3.xml']->getName());
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $children['test4.xml']);
    $this->assertEquals('test4.xml', $children['test4.xml']->getName());
    
    $dada = $children['dada'];
    $children = $dada->getChildren();
    $this->assertEquals(2, count($children));
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $children['test4.xml']);
    $this->assertEquals('test4.xml', $children['test4.xml']->getName());
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $children['test5.xml']);
    $this->assertEquals('test5.xml', $children['test5.xml']->getName());
    
  }
  
  public function testGetChild()
  {
    $col = new CollectionNode($this->tree, '');
    
    $result = $col->getChild('test1.xml');
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $result);
    $this->assertEquals('test1.xml', $result->getName());
    
    $col = $col->getChild('dir');
    
    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $col);
    $this->assertEquals('dir', $col->getName());
    
    $result = $col->getChild('test3.xml');
    
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $result);
    $this->assertEquals('test3.xml', $result->getName());
    
    $col = $col->getChild('dada');
    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $col);
    $this->assertEquals('dada', $col->getName());
    
    $result = $col->getChild('test4.xml');
    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $col);
    $this->assertEquals('dada', $col->getName());
  }
  
  public function testChildExists()
  {
    $col = new CollectionNode($this->tree);
    $col->path = '';
    
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
