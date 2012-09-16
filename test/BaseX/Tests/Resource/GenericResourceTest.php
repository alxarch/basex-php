<?php

namespace BaseX\Tests;


use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Generic;
use BaseX\StreamWrapper;

class GenericResource extends Generic
{}

class GenericResourceTest extends TestCaseDb
{
  protected $raw = '<resource raw="true" content-type="image/jpeg" modified-date="2012-05-27T12:36:48.000Z" size="60751">image.jpg</resource>';
  
  protected $xml = '<resource raw="false" content-type="application/xml" modified-date="2012-05-27T13:38:33.988Z">collection/doc.xml</resource>';
  
  public function setUp()
  {
    parent::setUp();
    StreamWrapper::register($this->session);
    $this->raw = new GenericResource($this->session, $this->dbname, 'image.jpg',  $this->raw);
    $this->xml = new GenericResource($this->session, $this->dbname, 'collection/doc.xml', $this->xml);
  }
  
  public function tearDown()
  {
    StreamWrapper::unregister();
    parent::tearDown();
    
  }
  
  public function testSize()
  {
    $this->assertEquals(0, $this->xml->getSize());
    $this->assertEquals('60751', $this->raw->getSize());
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
    
    $this->assertEquals('SimpleXMLElement', get_class($info));
    $this->assertTrue(isset($info['content-type']));
    $this->assertTrue(isset($info['raw']));
    $this->assertTrue(isset($info['modified-date']));
  }
  
  public function testFromUri()
  {
    $this->db->add('test.xml', '<test/>');
    $res = GenericResource::fromURI($this->session, "basex://$this->dbname/test.xml");
    $this->assertInstanceOf('BaseX\Tests\GenericResource', $res);
    $this->assertEquals('test.xml', $res->getPath());
    $this->assertFalse($res->isRaw());
  }
  
  public function testCopy()
  {
    $this->db->add('original.xml', '<test/>');
    
    $original = new GenericResource($this->session, $this->dbname, 'original.xml');
    
    $copy = $original->copy('copy.xml');
    
    $this->assertInstanceOf('BaseX\Tests\GenericResource', $copy);
    $this->assertEquals('copy.xml', $copy->getPath());
    
    $this->assertContains('copy.xml', $this->ls());
    $this->assertContains('original.xml', $this->ls());
    
    $this->assertEquals('<test/>', $this->doc('copy.xml'));
  }
  
  public function testCopyRaw()
  {
    $contents = md5(time());
    $this->db->store('original.txt', $contents);
    
    $original = new GenericResource($this->session, $this->dbname, 'original.txt');
    
    $copy = $original->copy('copy.txt');
    
    $this->assertInstanceOf('BaseX\Tests\GenericResource', $copy);
    $this->assertEquals('copy.txt', $copy->getPath());
    
    $this->assertContains('copy.txt', $this->ls());
    $this->assertContains('original.txt', $this->ls());
    
    $this->assertEquals($contents, $this->raw('copy.txt'));
  }
  
  public function testMove()
  {
    $this->db->add('original.xml', '<test/>');
    
    $original = new GenericResource($this->session, $this->dbname, 'original.xml');
    
    $copy = $original->move('moved.xml');
    
    $this->assertInstanceOf('BaseX\Tests\GenericResource', $copy);
    $this->assertEquals('moved.xml', $copy->getPath());
    
    $this->assertContains('moved.xml', $this->ls());
    $this->assertNotContains('original.xml', $this->ls());
    
    $this->assertEquals('<test/>', $this->doc('moved.xml'));
  }
  
  public function testMoveSameDest()
  {
    $this->db->add('original.xml', '<test/>');
    
    $original = new GenericResource($this->session, $this->dbname, 'original.xml');
    
    $original_time = $original->getModified();
    
    $moved = $original->move('original.xml');
    
    $moved_time = $moved->getModified();
    
    $this->assertNotEquals($moved_time, $original_time);
    
    $this->assertInstanceOf('BaseX\Tests\GenericResource', $moved);
    
    $this->assertEquals('original.xml', $moved->getPath());
    
    $this->assertContains('original.xml', $this->ls());
    
    $this->assertEquals('<test/>', $this->doc('original.xml'));
  }
  
  public function testDelete()
  {
    $this->db->add('original.xml', '<test/>');
    
    $original = new GenericResource($this->session, $this->dbname, 'original.xml');
    
    $original->delete();
    
    $this->assertNull($original->getPath());
    
    $this->assertNotContains('original.xml', $this->ls());
    
  }
  
  public function testGetContents()
  {
    $this->db->add('original.xml', '<test/>');
    
    $original = new GenericResource($this->session, $this->dbname, 'original.xml');
    
    $this->assertXmlStringEqualsXmlString('<test/>', $original->getContents());
  }
  
  public function testGetContentsInto()
  {
    $into = fopen('php://temp', 'r+');
    
    $this->db->add('original.xml', '<test/>');
    
    $original = new GenericResource($this->session, $this->dbname, 'original.xml');
    
    $result = $original->getContents($into);
    
    $this->assertFalse(false === $result);
    $this->assertTrue(is_int($result));
    $this->assertTrue($result > 0);
    
    rewind($into);
    
    $contents = stream_get_contents($into);
    
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
    
    fclose($into);
  }
  
  public function testGetContentsRawInto()
  {
    $into = fopen('php://temp', 'r+');
    
    $contents = md5(time());
    $this->db->store('test.txt', $contents);
    
    $original = new GenericResource($this->session, $this->dbname, 'test.txt');
    
    $result = $original->getContents($into);
    
    $this->assertFalse(false === $result);
    $this->assertTrue(is_int($result));
    $this->assertTrue($result > 0);
    
    rewind($into);
    
    $actual = stream_get_contents($into);
    
    $this->assertEquals($contents, $actual);
    
    fclose($into);
    
  }
  public function testGetContentsRaw()
  {
    $contents = md5(time());
    
    $this->db->store('test.txt', $contents);
   
    $original = new GenericResource($this->session, $this->dbname, 'test.txt');
    
    $this->assertEquals($contents, $original->getContents());
  }
  
  

  
}