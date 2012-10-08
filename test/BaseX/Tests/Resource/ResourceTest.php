<?php

namespace BaseX\Tests;


use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource;
use BaseX\StreamWrapper;

class GenericResource extends Resource
{
  protected function getCopyQuery($dest) {
    ;
  }
  
  protected function getMoveQuery($dest) {
    ;
  }
  
  protected function getDeleteQuery() {
    ;
  }
  
  protected function getContentsQuery() {
    ;
  }
}

class ResourceTest extends TestCaseDb
{
  protected $rawInfo = '<resource raw="true" content-type="image/jpeg" modified-date="2012-05-27T12:36:48.000Z" size="60751">image.jpg</resource>';
  protected $xmlInfo = '<resource raw="false" content-type="application/xml" modified-date="2012-05-27T13:38:33.988Z">collection/doc.xml</resource>';
  protected $raw;
  protected $xml;
  
  
  public function setUp()
  {
    parent::setUp();
    StreamWrapper::register($this->session);
    $this->raw = new GenericResource($this->session, $this->dbname, 'image.jpg');
    $this->raw->setInfo($this->rawInfo);
    
    $this->xml = new GenericResource($this->session, $this->dbname, 'collection/doc.xml');
    $this->xml->setInfo($this->xmlInfo);
  }
  
  public function tearDown()
  {
    StreamWrapper::unregister();
    parent::tearDown();
    
  }
  
  public function testSize()
  {
    $this->assertEquals(0, $this->xml->getSize());
    $this->assertEquals(60751, $this->raw->getSize());
  }
  
  public function testRaw()
  {
    $this->assertTrue($this->raw->isRaw());
    $this->assertFalse($this->xml->isRaw());
  }
  
  public function testType()
  {
    $this->assertEquals('image/jpeg', $this->raw->getType());
    $this->assertEquals('application/xml', $this->xml->getType());
  }
  
  public function testModified()
  {
    $this->assertEquals('2012-05-27T12:36:48.000Z', $this->raw->getModified());
    $this->assertEquals('2012-05-27T13:38:33.988Z', $this->xml->getModified());
  }
 
  public function testPath()
  {
    $this->assertEquals('image.jpg', $this->raw->getPath());
    $this->assertEquals('collection/doc.xml', $this->xml->getPath());
  }
  
  public function testGetName()
  {
    $this->assertEquals('image.jpg', $this->raw->getName());
    $this->assertEquals('doc.xml', $this->xml->getName());
  }
  
  public function testGetUri()
  {
    $this->assertEquals("basex://$this->dbname/image.jpg", $this->raw->getUri());
    $this->assertEquals("basex://$this->dbname/collection/doc.xml", $this->xml->getUri());
  }
  
  public function testGetStream()
  {
    $this->db->add('test.xml', '<root/>');
    
    $res = new GenericResource($this->session, $this->dbname, 'test.xml');
    
    $this->assertTrue(is_resource($res->getStream()));
  }
  
  
  public function testGetInfo()
  {
    $path = 'test.xml';
    $this->db->add($path, '<test/>');
    $doc = new GenericResource($this->session, $this->dbname, $path);
    $info = $doc->getInfo();
    
    $this->assertInstanceOf('BaseX\Resource\ResourceInfo', $info);
  }
  
  public function testFromUri()
  {
    $this->db->add('test.xml', '<test/>');
    $res = GenericResource::fromURI($this->session, "basex://$this->dbname/test.xml");
    $this->assertInstanceOf('BaseX\Tests\GenericResource', $res);
    $this->assertEquals('test.xml', $res->getPath());
    $this->assertFalse($res->isRaw());
  }
  
}