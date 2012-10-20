<?php

namespace BaseX\Dav;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Dav\ResourceNode;
use BaseX\Dav\ObjectTree;
use BaseX\StreamWrapper;

/**
 * Test class for ResourceNode.
 * Generated by PHPUnit on 2012-10-15 at 02:54:43.
 */
class ResourceNodeTest extends TestCaseDb {

  /**
   * @var ResourceNode
   */
  protected $node;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  public function setUp() {
    parent::setUp();
    $this->tree = new ObjectTree($this->db);
    $this->db->add('test.xml', '<test/>');
    $this->db->store('sa/test.txt', 'test');
    $this->node = new ResourceNode($this->tree, 'test.xml');
  }


  /**
   * @covers BaseX\Dav\ResourceNode::__construct
   */
  public function testConst() {
    $this->node = new ResourceNode($this->tree, 'test.xml');
    
  }
  /**
   * @covers BaseX\Dav\ResourceNode::getLastModified
   */
  public function testGetLastModified() {
    
    $this->assertNull($this->node->getLastModified());
    $node = $this->tree->getNodeForPath('test.xml');
    $this->assertNotNull($node->getLastModified());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::getName
   */
  public function testGetName() {
    $this->assertEquals('test.xml', $this->node->getName());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::getSize
   */
  public function testGetSize() {
    $this->assertEquals(null, $this->node->getSize());
    $raw = $this->tree->getNodeForPath('sa/test.txt');
    $this->assertNotEquals(0, $raw->getSize());
    $x = $this->tree->getNodeForPath('test.xml');
    $this->assertEquals(2, $x->getSize());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::getContentType
   */
  public function testGetContentType() {
    $this->assertEquals('text/plain', $this->tree->getNodeForPath('sa/test.txt')->getContentType());
    $this->assertEquals('application/xml', $this->tree->getNodeForPath('test.xml')->getContentType());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::setName
   */
  public function testSetName() {
    $node = $this->tree->getNodeForPath('sa/test.txt');
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
    $node = $this->tree->getNodeForPath('sa/test.txt');
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
    $node = $this->tree->getNodeForPath('sa/test.txt');
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
    $node = $this->tree->getNodeForPath('sa/test.txt');
    $this->assertNotEmpty($node->getEtag());
  }

  /**
   * @covers BaseX\Dav\ResourceNode::delete
   */
  public function testDelete() {
    $node = $this->tree->getNodeForPath('sa/test.txt');
    $node->delete();
    $this->assertNotContains('sa/test.txt', $this->ls());
  }

}

