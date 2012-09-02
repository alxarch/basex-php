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
    $this->db->add('test.xml', '<test/>');
    $doc = new Resource($this->db, 'test.xml');
    $this->assertInstanceOf('BaseX\Resource', $doc);
    
    $this->db->store('test.txt', 'test');
    $doc = new Resource($this->db, 'test.txt');
    $this->assertInstanceOf('BaseX\Resource', $doc);
    
    $this->db->delete('text.xml');
    $this->db->delete('text.txt');
  }
  
  public function testGetPath()
  {
    $path = 'test.xml';
    $this->db->add($path, '<test/>');
    $doc = new Resource($this->db, $path);
    $this->assertEquals($path, $doc->getPath());
    $this->db->delete($path);
  }
  
  public function testGetInfo()
  {
    $path = 'test.xml';
    $this->db->add($path, '<test/>');
    $doc = new Resource($this->db, $path);
    $info = $doc->getInfo();
    
    $this->assertInstanceOf('BaseX\Resource\Info', $info);

    $this->assertEquals($info->path(), $doc->getPath());
    
    $this->db->delete($path);
  }
  
  public function testIsRaw()
  {
    $this->db->add('test.xml', '<test/>');
    
    $doc = new Resource($this->db, 'test.xml');
    $this->assertFalse($doc->isRaw());
    
    $this->db->delete('test.xml');
    
    $this->db->store('test.txt', 'test');
    $doc = new Resource($this->db, 'test.txt');
    $this->assertTrue($doc->isRaw());
    
    $this->db->delete('test.txt');
  }
  
  public function testGetDatabase()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Resource($this->db, 'test.xml');
    $db = $doc->getDatabase();
    $this->assertInstanceOf('BaseX\Database', $db);
    $this->assertTrue($db === $this->db);
    $this->db->delete('test.xml');
  }
  
  public function testGetContents()
  {
    $this->db->add('te st.xml', '<test/>');
    $doc = new Resource($this->db, 'te st.xml');
    
    $contents = $doc->getContents();
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
    $this->db->delete('te st.xml');
    
    $this->db->store('te st.txt', 'test');
    $doc = new Resource($this->db, 'te st.txt');
    $contents = $doc->getContents();
    $this->assertEquals('test', $contents);
    $this->db->delete('te st.txt');
  }
  
  public function testSave()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Resource($this->db, 'test.xml');
    $old = $doc->getInfo();
    
    $result = $doc->save();
    
    $new = $doc->getInfo();
    $this->assertTrue($result === $doc);
    $this->assertNotEquals($new->modified(), $old->modified());
    
    $this->db->delete('test.xml');
  }
  
  public function testCopy()
  {
    $this->db->add('test.xml', '<test/>');
    $original = new Resource($this->db, 'test.xml');
    
    $copy = $original->copy('copy.xml');
    
    $this->assertInstanceOf('BaseX\Resource', $copy);
    
    $this->assertEquals('copy.xml', $copy->getPath());
    
    $this->assertContains('copy.xml', self::ls());
    
    $contents = self::doc('copy.xml');
    
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
        
    $this->db->delete('test.xml');
  }
  
  public function testMove()
  {
    $this->db->add('test.xml', '<test/>');
    $original = new Resource($this->db, 'test.xml');
    
    $result = $original->move('moved.xml');
    
    $this->assertTrue($result === $original);
    
    $this->assertEquals($result->getPath(), 'moved.xml');
    $this->assertXmlStringEqualsXmlString('<test/>', $result->getContents());
    
    $contents = self::doc('moved.xml');
    $this->assertEquals($contents, '<test/>');
    
    $this->assertNotContains('test.xml', self::ls());
    
    $this->db->delete('moved.xml');
  }
  
  
  public function testDelete()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Resource($this->db, 'test.xml');
    $doc->delete();
    
    $this->assertNull($doc->getPath());
    $this->assertNotContains('test.xml', self::ls());
    $this->db->delete('test.xml');
  }
  
  public function testReloadInfo()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Resource($this->db, 'test.xml');
    $old = $doc->getInfo();
    $this->db->replace('test.xml', '<other/>');
    $result = $doc->reloadInfo();
    $new = $doc->getInfo();
    $this->assertTrue($result === $doc);
    $this->assertNotEquals($new->modified(), $old->modified());
    $this->db->delete('test.xml');
  }
  
  /**
   * @depends testGetContents 
   */
  public function testSetContents()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Resource($this->db, 'test.xml');
    
    $result = $doc->setContents('<other/>');
    $this->assertTrue($result === $doc);
    $this->assertXmlStringEqualsXmlString('<other/>', $doc->getContents());
    $this->db->delete('test.xml');
    
  }
  
  /**
   *  @depends testSetContents 
   *  @depends testSave
   */
  public function testSetContentsAndSave()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Resource($this->db, 'test.xml');
    
    $doc->setContents('<other/>')->save();
    
    $contents = self::doc('test.xml');
    $this->assertXmlStringEqualsXmlString('<other/>', $contents);
    $this->db->delete('test.xml');
  }
  
  public function testReload()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Resource($this->db, 'test.xml');
    
    $this->db->replace('test.xml', '<new/>');
    
    $xql = "db:list-details('".$this->dbname."', 'test.xml')/@modified-date/string()";
    $modified = $this->session->query($xql)->execute();
    
    $result = $doc->reload();
    
    $this->assertInstanceOf('BaseX\Resource', $result);
    $this->assertXmlStringEqualsXmlString('<new/>', $doc->getContents());
    $this->assertEquals($modified, $doc->getInfo()->modified());
    
    $this->db->delete('test.xml');
  }
  

  public function testEtag()
  {
    $this->db->add('test.xml', '<test/>');
    $db = $this->dbname;
    $xql = "max(db:list-details('$db', 'test.xml')/@modified-date/string())";
    $time = $this->session->query($xql)->execute();
    $etag = md5("$db/test.xml/$time");
    $resource = new Resource($this->db, 'test.xml');
    $this->assertEquals($etag, $resource->etag());
    
    $this->db->replace('test.xml', '<new/>');
    $resource->reloadInfo();
    $this->assertNotEquals($resource->etag(), $etag);
    $this->db->delete('test.xml');
  }
}