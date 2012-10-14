<?php

namespace BaseX\Resource;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource;
use BaseX\Helpers as B;

class GenericResource extends Resource
{
  public function refresh(){}
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

    $this->raw = new GenericResource($this->db, 'image.jpg', B::date('2012-05-27T12:36:48.000Z'));
    $this->xml = new GenericResource($this->db, 'collection/doc.xml', B::date('2012-05-27T13:38:33.988Z'));

  }
 
  public function testModified()
  {
    
    $this->assertEquals(B::date('2012-05-27T12:36:48.000Z'), $this->raw->getModified());
    $this->assertEquals(B::date('2012-05-27T13:38:33.988Z'), $this->xml->getModified());
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
  
  public function testDelete()
  {
    $this->db->store('original.txt', 'test');
    
    $original = new GenericResource($this->db, 'original.txt');
    
    $original->delete();
    
    $this->assertNotContains('original.txt', $this->ls());
    
  }
    
  public function testCopy()
  {
    $contents = md5(time());
    $this->db->store('original.txt', $contents);
    
    $original = new GenericResource($this->db, 'original.txt');
    
    $original->copy('copy.txt');
    
    $this->assertContains('copy.txt', $this->ls());
    $this->assertContains('original.txt', $this->ls());
    
    $this->assertEquals($contents, $this->raw('copy.txt'));
  }
  
   public function testCopyDoc()
  {
    $this->db->add('original.xml', '<test/>');
    
    $original = new GenericResource($this->db, 'original.xml');
    
    $original->copy('copy.xml');
    
    $this->assertContains('copy.xml', $this->ls());
    $this->assertContains('original.xml', $this->ls());
    
    $this->assertEquals('<test/>', $this->doc('copy.xml'));
  }
  
  public function testMove()
  {
    $this->db->store('original.txt', 'test');
    
    $original = new GenericResource($this->db, 'original.txt');
    
    $original->move('moved.txt');
    
    $this->assertContains('moved.txt', $this->ls());
    $this->assertNotContains('original.txt', $this->ls());
    
    $this->assertEquals('test', $this->raw('moved.txt'));
  }
  
  public function testMoveCollection()
  {
    $this->db->store('test/original-1.txt', 'test');
    $this->db->store('test/original-2.txt', 'test');
    
    $collection = new GenericResource($this->db, 'test');
    
    $collection->move('moved');
    
    $this->assertContains('moved/original-1.txt', $this->ls());
    $this->assertContains('moved/original-2.txt', $this->ls());
    $this->assertNotContains('test/original-1.txt', $this->ls());
    $this->assertNotContains('test/original-2.txt', $this->ls());
    
  }
  
}