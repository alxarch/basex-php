<?php

namespace BaseX\Tests;

use BaseX\TestCaseDb;
use BaseX\Session;
use BaseX\Resource;
use BaseX\Database;

class ResourceTest extends TestCaseDb
{
  public function testInit()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Resource(self::$db, 'test.xml');
    $this->assertInstanceOf('BaseX\Resource', $doc);
    
    self::$db->store('test.txt', 'test');
    $doc = new Resource(self::$db, 'test.txt');
    $this->assertInstanceOf('BaseX\Resource', $doc);
    
    self::$db->delete('text.xml');
    self::$db->delete('text.txt');
  }
  
  public function testGetPath()
  {
    $path = 'test.xml';
    self::$db->add($path, '<test/>');
    $doc = new Resource(self::$db, $path);
    $this->assertEquals($path, $doc->getPath());
    self::$db->delete($path);
  }
  
  public function testGetInfo()
  {
    $path = 'test.xml';
    self::$db->add($path, '<test/>');
    $doc = new Resource(self::$db, $path);
    $info = $doc->getInfo();
    
    $this->assertInstanceOf('BaseX\Resource\Info', $info);

    $this->assertEquals($info->path(), $doc->getPath());
    
    self::$db->delete($path);
  }
  
  public function testIsRaw()
  {
    self::$db->add('test.xml', '<test/>');
    
    $doc = new Resource(self::$db, 'test.xml');
    $this->assertFalse($doc->isRaw());
    
    self::$db->delete('test.xml');
    
    self::$db->store('test.txt', 'test');
    $doc = new Resource(self::$db, 'test.txt');
    $this->assertTrue($doc->isRaw());
    
    self::$db->delete('test.txt');
  }
  
  public function testGetDatabase()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Resource(self::$db, 'test.xml');
    $db = $doc->getDatabase();
    $this->assertInstanceOf('BaseX\Database', $db);
    $this->assertTrue($db === self::$db);
    self::$db->delete('test.xml');
  }
  
  public function testGetContents()
  {
    self::$db->add('te st.xml', '<test/>');
    $doc = new Resource(self::$db, 'te st.xml');
    
    $contents = $doc->getContents();
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
    self::$db->delete('te st.xml');
    
    self::$db->store('te st.txt', 'test');
    $doc = new Resource(self::$db, 'te st.txt');
    $contents = $doc->getContents();
    $this->assertEquals('test', $contents);
    self::$db->delete('te st.txt');
  }
  
  public function testSave()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Resource(self::$db, 'test.xml');
    $old = $doc->getInfo();
    
    $result = $doc->save();
    
    $new = $doc->getInfo();
    $this->assertTrue($result === $doc);
    $this->assertNotEquals($new->modified(), $old->modified());
    
    self::$db->delete('test.xml');
  }
  
  public function testCopy()
  {
    self::$db->add('test.xml', '<test/>');
    $original = new Resource(self::$db, 'test.xml');
    
    $copy = $original->copy('copy.xml');
    
    $this->assertInstanceOf('BaseX\Resource', $copy);
    
    $this->assertEquals('copy.xml', $copy->getPath());
    
    $this->assertContains('copy.xml', self::ls());
    
    $contents = self::doc('copy.xml');
    
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
        
    self::$db->delete('test.xml');
  }
  
  public function testMove()
  {
    self::$db->add('test.xml', '<test/>');
    $original = new Resource(self::$db, 'test.xml');
    
    $result = $original->move('moved.xml');
    
    $this->assertTrue($result === $original);
    
    $this->assertEquals($result->getPath(), 'moved.xml');
    $this->assertXmlStringEqualsXmlString('<test/>', $result->getContents());
    
    $contents = self::doc('moved.xml');
    $this->assertEquals($contents, '<test/>');
    
    $this->assertNotContains('test.xml', self::ls());
    
    self::$db->delete('moved.xml');
  }
  
  
  public function testDelete()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Resource(self::$db, 'test.xml');
    $doc->delete();
    
    $this->assertNull($doc->getPath());
    $this->assertNotContains('test.xml', self::ls());
    self::$db->delete('test.xml');
  }
  
  public function testReloadInfo()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Resource(self::$db, 'test.xml');
    $old = $doc->getInfo();
    self::$db->replace('test.xml', '<other/>');
    $result = $doc->reloadInfo();
    $new = $doc->getInfo();
    $this->assertTrue($result === $doc);
    $this->assertNotEquals($new->modified(), $old->modified());
    self::$db->delete('test.xml');
  }
  
  /**
   * @depends testGetContents 
   */
  public function testSetContents()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Resource(self::$db, 'test.xml');
    
    $result = $doc->setContents('<other/>');
    $this->assertTrue($result === $doc);
    $this->assertXmlStringEqualsXmlString('<other/>', $doc->getContents());
    self::$db->delete('test.xml');
    
  }
  
  /**
   *  @depends testSetContents 
   *  @depends testSave
   */
  public function testSetContentsAndSave()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Resource(self::$db, 'test.xml');
    
    $doc->setContents('<other/>')->save();
    
    $contents = self::doc('test.xml');
    $this->assertXmlStringEqualsXmlString('<other/>', $contents);
    self::$db->delete('test.xml');
  }
  
  public function testReload()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Resource(self::$db, 'test.xml');
    
    self::$db->replace('test.xml', '<new/>');
    
    $xql = "db:list-details('".self::$dbname."', 'test.xml')/@modified-date/string()";
    $modified = self::$session->query($xql)->execute();
    
    $result = $doc->reload();
    
    $this->assertInstanceOf('BaseX\Resource', $result);
    $this->assertXmlStringEqualsXmlString('<new/>', $doc->getContents());
    $this->assertEquals($modified, $doc->getInfo()->modified());
    
    self::$db->delete('test.xml');
  }
  

  public function testEtag()
  {
    self::$db->add('test.xml', '<test/>');
    $db = self::$dbname;
    $xql = "max(db:list-details('$db', 'test.xml')/@modified-date/string())";
    $time = self::$session->query($xql)->execute();
    $etag = md5("$db/test.xml/$time");
    $resource = new Resource(self::$db, 'test.xml');
    $this->assertEquals($etag, $resource->etag());
    
    self::$db->replace('test.xml', '<new/>');
    $resource->reloadInfo();
    $this->assertNotEquals($resource->etag(), $etag);
    self::$db->delete('test.xml');
  }
}