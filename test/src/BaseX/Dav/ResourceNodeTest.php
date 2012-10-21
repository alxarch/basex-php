<?php

namespace BaseX\Dav;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Dav\ResourceNode;
use BaseX\StreamWrapper;
use BaseX\Dav\Iterator\Nodes;

/**
 * Test class for ResourceNode.
 * Generated by PHPUnit on 2012-10-15 at 02:54:43.
 */
class ResourceNodeTest extends TestCaseDb {

  /**
   * @var \BaseX\Dav\ResourceNodeIterator
   */
  protected $nodes;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  public function setUp() {
    parent::setUp();
    
    $this->db->add('test.xml', '<test/>');
    $this->db->store('sa/test.txt', 'test');
    $nodes =  new Nodes($this->db, '');
    $this->nodes = $nodes->withTimestamps()->getIterator();

  }


  /**
   * @covers BaseX\Dav\ResourceNode::__construct
   */
  public function testConst() {
    $node = new ResourceNode($this->db, 'test.xml');
    
  }
  /**
   * @covers BaseX\Dav\ResourceNode::getLastModified
   */
  public function testGetLastModified() {
    
    $this->assertNotNull($this->nodes[0]->getLastModified());
    
  }

  /**
   * @covers BaseX\Dav\ResourceNode::getName
   */
  public function testGetName() {
    $this->assertEquals('test.xml', $this->nodes[1]->getName());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::getSize
   */
  public function testGetSize() {
    $this->assertEquals(4, $this->nodes[0]->getSize());
    
    $this->assertEquals(0, $this->nodes[1]->getSize());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::getContentType
   */
  public function testGetContentType() {
    $this->assertEquals('text/plain', $this->nodes[0]->getContentType());
    $this->assertEquals('application/xml', $this->nodes[1]->getContentType());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::setName
   */
  public function testSetName() {
    $node = $this->nodes[0];
    $node->setName('zam.txt');
    $this->assertContains('sa/zam.txt', $this->ls());
    $this->assertNotContains('sa/test.txt', $this->ls());
    $this->assertEquals('zam.txt', $node->getName());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::put
   */
  public function testPut() {
    StreamWrapper::register($this->session);
    $node = $this->nodes[0];
    $node->put('ratata');
    $this->assertEquals('ratata', $this->raw('sa/test.txt'));
    
    $in = fopen(DATADIR.'/test.jpg', 'r');
    $node->put($in);
    fclose($in);
    
    StreamWrapper::unregister();
  }

  /**
   * @covers BaseX\Dav\ResourceNode::get
   */
  public function testGet() {
    StreamWrapper::register($this->session);
    $node = $this->nodes[0];
    $stream = $node->get();
    $this->assertTrue(is_resource($stream));
    $data = fread($stream, 100);
    fclose($stream);
    $this->assertEquals($data, $this->raw('sa/test.txt'));
    StreamWrapper::unregister();
    
  }

  /**
   * @covers BaseX\Dav\ResourceNode::getETag
   */
  public function testGetETag() {
       $node = $this->nodes[0];
    $this->assertNotEmpty($node->getEtag());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::delete
   */
  public function testDelete() {
        $node = $this->nodes[0];
    $node->delete();
    $this->assertNotContains('sa/test.txt', $this->ls());
  }

}

