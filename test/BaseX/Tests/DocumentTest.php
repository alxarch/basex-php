<?php

namespace BaseX\Tests;

use BaseX\TestCaseDb;
use BaseX\Session;
use BaseX\Document;
use BaseX\Database;

class DocumentTest extends TestCaseDb
{
  public function testInit()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    $this->assertInstanceOf('BaseX\Document', $doc);
    
    $this->db->store('test.txt', 'test');
    $doc = new Document($this->db, 'test.txt');
    $this->assertInstanceOf('BaseX\Document', $doc);
    
  }
  
  public function testGetPath()
  {
    $path = 'test.xml';
    $this->db->add($path, '<test/>');
    $doc = new Document($this->db, $path);
    $this->assertEquals($path, $doc->getPath());
  }
  
  public function testGetInfo()
  {
    $path = 'test.xml';
    $this->db->add($path, '<test/>');
    $doc = new Document($this->db, $path);
    $info = $doc->getInfo();
    $this->assertInstanceOf('BaseX\Document\Info', $info);
    

    $this->assertEquals($info->path(), $doc->getPath());
  }
  
  public function testIsRaw()
  {
    $path = 'test.xml';
    $this->db->add($path, '<test/>');
    $doc = new Document($this->db, $path);
    $this->assertFalse($doc->isRaw());
    
    $this->db->store('test.txt', 'test');
    $doc = new Document($this->db, 'test.txt');
    $this->assertTrue($doc->isRaw());
  }
  
  public function testGetDatabase()
  {
    $path = 'test.xml';
    $this->db->add($path, '<test/>');
    $doc = new Document($this->db, $path);
    $db = $doc->getDatabase();
    $this->assertInstanceOf('BaseX\Database', $db);
    $this->assertTrue($db === $this->db);
  }
  
  public function testGetContents()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    
    $contents = $doc->getContents();
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
    
    $this->db->store('test.txt', 'test');
    $doc = new Document($this->db, 'test.txt');
    $contents = $doc->getContents();
    $this->assertEquals('test', $contents);
  }
  
  public function testSave()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    $old = $doc->getInfo();
    
    $result = $doc->save();
    
    $new = $doc->getInfo();
    $this->assertTrue($result === $doc);
    $this->assertNotEquals($new->modified(), $old->modified());
    
  }
  
  public function testCopy()
  {
    $this->db->add('test.xml', '<test/>');
    $original = new Document($this->db, 'test.xml');
    
    $copy = $original->copy('copy.xml');
    
    $this->assertInstanceOf('BaseX\Document', $copy);
    
    $this->assertEquals('copy.xml', $copy->getPath());
    
    $this->assertContains('copy.xml', $this->ls());
    
    $contents = $this->doc('copy.xml');
    
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
        
  }
  
  public function testMove()
  {
    $this->db->add('test.xml', '<test/>');
    $original = new Document($this->db, 'test.xml');
    
    $result = $original->move('moved.xml');
    
    $this->assertTrue($result === $original);
    
    $this->assertEquals($result->getPath(), 'moved.xml');
    $this->assertXmlStringEqualsXmlString('<test/>', $result->getContents());
    
    $contents = $this->doc('moved.xml');
    $this->assertEquals($contents, '<test/>');
    
    $this->assertNotContains('test.xml', $this->ls());
  }
  
  
  public function testDelete()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    $doc->delete();
    
    $this->assertNull($doc->getPath());
    $this->assertNotContains('test.xml', $this->ls());
  }
  
  public function testGetXML()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    
    $xml = $doc->getXML();
    
    $this->assertInstanceOf('\DOMDocument', $xml);
    
  }
  
  public function testReloadInfo()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    $old = $doc->getInfo();
    $this->db->replace('test.xml', '<other/>');
    $result = $doc->reloadInfo();
    $new = $doc->getInfo();
    $this->assertTrue($result === $doc);
    $this->assertNotEquals($new->modified(), $old->modified());
  }
  
  /**
   * @depends testGetContents 
   */
  public function testSetContents()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    
    $result = $doc->setContents('<other/>');
    $this->assertTrue($result === $doc);
    $this->assertXmlStringEqualsXmlString('<other/>', $doc->getContents());
    
  }
  
  /**
   *  @depends testSetContents 
   *  @depends testSave
   */
  public function testSetContentsAndSave()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    
    $doc->setContents('<other/>')->save();
    
    $contents = $this->doc('test.xml');
    $this->assertXmlStringEqualsXmlString('<other/>', $contents);
  }
}