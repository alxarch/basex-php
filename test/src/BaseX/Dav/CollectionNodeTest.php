<?php

namespace BaseX\Dav;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Dav\CollectionNode;
use BaseX\StreamWrapper;

class CollectionNodeTest extends TestCaseDb
{
  protected $collection;
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
    
    $this->collection = new CollectionNode($this->db, '');
  }

  public function testGetChildren()
  {
    $children = $this->collection->getChildren();
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
    $this->collection = new CollectionNode($this->db, '');

    $node = $this->collection->getChild('test1.xml');
    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $node);
    $this->assertEquals('test1.xml', $node->getName());

    $this->collection = $this->collection->getChild('dir');

    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $this->collection);
    $this->assertEquals('dir', $this->collection->getName());

    $node = $this->collection->getChild('test3.xml');

    $this->assertInstanceOf('BaseX\Dav\ResourceNode', $node);
    $this->assertEquals('test3.xml', $node->getName());

    $this->collection = $this->collection->getChild('dada');
    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $this->collection);
    $this->assertEquals('dada', $this->collection->getName());

    $node = $this->collection->getChild('test4.xml');
    $this->assertInstanceOf('BaseX\Dav\CollectionNode', $this->collection);
    $this->assertEquals('dada', $this->collection->getName());
  }

  public function testChildExists()
  {
    $this->assertTrue($this->collection->childExists('test1.xml'));

    $this->assertTrue($this->collection->childExists('dir'));
    $this->assertFalse($this->collection->childExists('nothere.png'));
    $this->assertFalse($this->collection->childExists('test3.xml'));
    $this->assertTrue($this->collection->childExists('dir/test3.xml'));

    $this->collection = $this->collection->getChild('dir');
    $this->assertTrue($this->collection->childExists('dada'));
    $this->assertFalse($this->collection->childExists('nothere.png'));
    $this->assertTrue($this->collection->childExists('test3.xml'));
    $this->assertFalse($this->collection->childExists('test1.xml'));

    $this->collection = $this->collection->getChild('dada');
    $this->assertTrue($this->collection->childExists('test4.xml'));
    $this->assertTrue($this->collection->childExists('test5.xml'));
    $this->assertFalse($this->collection->childExists('nothere.png'));
  }

  public function testCreateFile()
  {

    $etag = $this->collection->createFile('dir/test.xml', '<test/>');

    $this->assertTrue($this->db->exists('dir/test.xml'));

    $this->assertNotNull($etag);

    $etag = $this->collection->createFile('dir/test.txt', 'test');

    $this->assertNotNull($etag);

    $this->assertTrue($this->db->exists('dir/test.txt'));
    $xql = "db:list-details('$this->dbname', 'dir/test.txt')/@size/string()";

    $this->assertFalse('0' === $this->session->query($xql)->execute());
  }

  public function tearDown()
  {
    StreamWrapper::unregister();
    parent::tearDown();
  }

}
