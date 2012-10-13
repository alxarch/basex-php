<?php

namespace BaseX\Resource;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Document;
use BaseX\StreamWrapper;

class DocumentTest extends TestCaseDb
{
  
  function setUp()
  {
    parent::setUp();
    StreamWrapper::register($this->session);
  }
  
  public function testGetXML()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    
    $xml = $doc->getXML();
    
    $this->assertInstanceOf('\DOMDocument', $xml);
    $this->db->delete('test.xml');
  }
  
  public function testXpath()
  {
    $this->db->add('test-1.xml', '<root><test/><test/></root>');
    $this->db->add('test-2.xml', '<root><test/></root>');
    
    $doc = new Document($this->db, 'test-1.xml');
    
    $result = $doc->xpath('//test');
    
    $this->assertNotEmpty($result);
    
    foreach ($result as $r)
    {
      $this->assertXmlStringEqualsXmlString('<test/>', $r);
    }
   
  }
    
 
  
  function tearDown()
  {
    StreamWrapper::unregister();
    parent::tearDown();
  }
  
  public function testRaw()
  {
    $doc = new Document($this->db, 'test-1.xml');
    $this->assertFalse($doc->isRaw());
  }
  
}