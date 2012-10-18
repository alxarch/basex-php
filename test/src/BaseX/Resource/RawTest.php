<?php

/**
 * @package BaseX
 * @subpackage Tests 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Raw;
use BaseX\StreamWrapper;

/**
 * BaseX Generic Resource tests for xml documents.
 * 
 * @package BaseX 
 */
class RawTest extends TestCaseDb
{
  protected $resource;
  function setUp()
  {
    parent::setUp();
    StreamWrapper::register($this->session);
    $this->contents = md5(time());
    $this->db->store('test.txt', $this->contents);
    $this->resource =  new Raw($this->db, 'test.txt');
  }
  
  public function testGetRaw()
  {
    $this->assertTrue($this->resource->isRaw());
    
  }
  public function testGetSize()
  {
    $this->resource->refresh();
    $this->assertEquals(32, $this->resource->getSize());
  }

  public function testGetFilePath()
  {
    $dbpath = $this->session->query('db:system()/mainoptions/dbpath/text()')->execute();
    $file = $dbpath.'/'.$this->dbname.'/raw/test.txt';
    $this->assertEquals($file, $this->resource->getFilepath());
  }
    
  function tearDown()
  {
    StreamWrapper::unregister();
    parent::tearDown();
  }
  
  
  function testGetContents() {
    $this->assertEquals($this->contents, $this->resource->getContents());
  }
  
  function testSetContents() {
    $this->resource->setContents('yadayada');
    $this->assertEquals('yadayada', $this->raw('test.txt'));
  }
  
  function testReadMethod()
  {
    $this->assertEquals('retrieve', $this->resource->getReadMethod());
  }
  
  function testWriteMethod()
  {
    $this->assertEquals('store', $this->resource->getWriteMethod());
  }
  
}