<?php

namespace BaseX\Resource;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Document;


class DocumentTest extends TestCaseDb
{
  /**
   *
   * @var \BaseX\Resource\Document
   */
  protected $resource;
  
  function setUp()
  {
    parent::setUp();
    $this->db->add('test.xml', '<test/>');
    $this->resource = new Document($this->db, 'test.xml');
  }
  
  public function testGetXML()
  {
    
    $xml = $this->resource->getXML();
    
    $this->assertInstanceOf('\DOMDocument', $xml);
    $this->assertXmlStringEqualsXmlString('<test/>', $xml->saveXML());
    
  }
  
  public function testXpath()
  {
    $this->db->replace('test.xml', '<root><test/><test/></root>');
    
    $result = $this->resource->xpath('//test');
    
    $this->assertNotEmpty($result);
    
    foreach ($result as $r)
    {
      $this->assertXmlStringEqualsXmlString('<test/>', $r);
    }
   
  }

  public function testSetContents()
  {
    $this->resource->setContents('<sada/>');
    $this->assertXmlStringEqualsXmlString($this->resource->getContents(), '<sada/>');
  }
  

  public function testRaw()
  {
    $this->assertFalse($this->resource->isRaw());
  }
  
   
  function testReadMethod()
  {
    $this->assertEquals('open', $this->resource->getReadMethod());
  }
  
  function testWriteMethod()
  {
    $this->assertEquals('replace', $this->resource->getWriteMethod());
  }
  
}