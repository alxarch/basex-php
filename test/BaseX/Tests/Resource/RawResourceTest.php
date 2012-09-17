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

/**
 * BaseX Generic Resource tests for xml documents.
 * 
 * @package BaseX 
 */
class RawResourceTest extends TestCaseDb
{
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
    
    $r = new \BaseX\Resource\ResourceInfo($info);
    $raw = new Raw($this->session, $this->dbname, 'test.txt', $r);
    
    $info = '<resource raw="false" content-type="application/xml" modified-date="2012-05-27T13:38:33.988Z">collection/doc.xml</resource>';
    
    $raw = new Raw($this->session, $this->dbname, 'test.txt', $info);
  }
  
  
  
}