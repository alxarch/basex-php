<?php

/**
 * @package BaseX
 * @subpackage Tests 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Tests;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Raw;
use BaseX\StreamWrapper;

/**
 * BaseX Generic Resource tests for xml documents.
 * 
 * @package BaseX 
 */
class RawResourceTest extends TestCaseDb
{
  function setUp()
  {
    parent::setUp();
    StreamWrapper::register($this->session);
  }
  
  function testInit()
  {
    $this->db->store('test.txt', 'yadayada');
    
    $raw = new Raw($this->session, $this->dbname, 'test.txt');
    
    $this->assertInstanceOf('BaseX\Resource', $raw);
    
  }
  
  /**
   * @expectedException BaseX\Error
   * @expectedExceptionMessage Resource is not a raw file.
   * 
   */
  function testInitNotRaw()
  {
    $this->db->add('test.xml', '<yada/>');
    $raw = new Raw($this->session, $this->dbname, 'test.xml');
  }
  
  /**
   * @expectedException BaseX\Error
   * @expectedExceptionMessage Resource is not a raw file.
   */
  function testConstructorWithInfo()
  {
    
    $this->db->store('test.txt', 'yadayada');
    $info = '<resource raw="true" content-type="image/jpeg" modified-date="2012-05-27T12:36:48.000Z" size="60751">image.jpg</resource>';
    
    $r = new \BaseX\Resource\ResourceInfo();
    $r->setSession($this->session)->setData($info);
    $raw = new Raw($this->session, $this->dbname, 'test.txt', $r);
    
    $info = '<resource raw="false" content-type="application/xml" modified-date="2012-05-27T13:38:33.988Z">collection/doc.xml</resource>';
    
    $raw = new Raw($this->session, $this->dbname, 'test.txt', $info);
  }
  
  public function testCopy()
  {
    $contents = md5(time());
    $this->db->store('original.txt', $contents);
    
    $original = new Raw($this->session, $this->dbname, 'original.txt');
    
    $copy = $original->copy('copy.txt');
    
    $this->assertInstanceOf('BaseX\Resource\Raw', $copy);
    $this->assertEquals('copy.txt', $copy->getPath());
    
    $this->assertContains('copy.txt', $this->ls());
    $this->assertContains('original.txt', $this->ls());
    
    $this->assertEquals($contents, $this->raw('copy.txt'));
  }
  
  public function testMove()
  {
    $this->db->store('original.txt', 'test');
    
    $original = new Raw($this->session, $this->dbname, 'original.txt');
    
    $copy = $original->move('moved.txt');
    
    $this->assertInstanceOf('BaseX\Resource\Raw', $copy);
    $this->assertEquals('moved.txt', $copy->getPath());
    
    $this->assertContains('moved.txt', $this->ls());
    $this->assertNotContains('original.txt', $this->ls());
    
    $this->assertEquals('test', $this->raw('moved.txt'));
  }
  
  public function testMoveSameDest()
  {
    $this->db->store('original.txt', 'test');
    
    $original = new Raw($this->session, $this->dbname, 'original.txt');
    
    $original_time = $original->getModified();
    
    $moved = $original->move('original.txt');
    
    $moved_time = $moved->getModified();
    
//    $this->assertNotEquals($moved_time, $original_time);
    
    $this->assertInstanceOf('BaseX\Resource\Raw', $moved);
    
    $this->assertEquals('original.txt', $moved->getPath());
    
    $this->assertContains('original.txt', $this->ls());
    
    $this->assertEquals('test', $this->raw('original.txt'));
  }
  
  public function testDelete()
  {
    $this->db->store('original.txt', 'test');
    
    $original = new Raw($this->session, $this->dbname, 'original.txt');
    
    $original->delete();
    
    $this->assertNull($original->getPath());
    
    $this->assertNotContains('original.txt', $this->ls());
    
  }

  
  public function testGetContentsRawInto()
  {
    $into = fopen('php://temp', 'r+');
    
    $contents = md5(time());
    $this->db->store('test.txt', $contents);
    
    $original = new Raw($this->session, $this->dbname, 'test.txt');
    
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
   
    $original = new Raw($this->session, $this->dbname, 'test.txt');
    
    $this->assertEquals($contents, $original->getContents());
  }
  
  public function testGetFilePath()
  {
    $contents = md5(time());
    $this->db->store('test.txt', $contents);
    $raw = new Raw($this->session, $this->dbname, 'test.txt');
    
    $dbpath = $this->session->query('db:system()/mainoptions/dbpath/text()')->execute();
    $file = $dbpath.'/'.$this->dbname.'/raw/test.txt';
    $this->assertFileExists($file);
    $this->assertEquals($file, $raw->getFilePath());
  }
    
  function tearDown()
  {
    StreamWrapper::unregister();
    parent::tearDown();
  }
}