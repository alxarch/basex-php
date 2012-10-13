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
  function setUp()
  {
    parent::setUp();
    StreamWrapper::register($this->session);
  }
  
  function testInit()
  {
    $this->db->store('test.txt', 'yadayada');
    
    $raw = new Raw($this->db, 'test.txt');
    
  }
  
  public function testGetRaw()
  {
    $raw = new Raw($this->db, 'test.x');
    $this->assertTrue($raw->isRaw());
    
  }
  public function testGetSize()
  {
    $raw = new Raw($this->db, 'test.x');
    $raw->setSize(120);
    
    $this->assertEquals(120, $raw->getSize());
  }

  public function testGetFilePath()
  {
    $contents = md5(time());
    $this->db->store('test.txt', $contents);
    $raw = new Raw($this->db, 'test.txt');
    $dbpath = $this->session->query('db:system()/mainoptions/dbpath/text()')->execute();
    $file = $dbpath.'/'.$this->dbname.'/raw/test.txt';
    $this->assertEquals($file, $raw->getFilepath());
  }
    
  function tearDown()
  {
    StreamWrapper::unregister();
    parent::tearDown();
  }
  
}