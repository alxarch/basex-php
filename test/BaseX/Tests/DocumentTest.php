<?php

namespace BaseX\Tests;

use BaseX\TestCaseDb;
use BaseX\Session;
use BaseX\Document;
use BaseX\Database;

class DocumentTest extends TestCaseDb
{
  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Specified resource is stored as raw file. 
   */
  public function testInit()
  {
    $this->db->store('test.txt', 'test');
    try
    {
      $doc = new Document($this->db, 'test.txt');
    }
    catch (InvalidArgumentException $e)
    {
      $this->db->delete('text.txt');
      throw $e;
    }
  }
  
  public function testGetXML()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->db, 'test.xml');
    
    $xml = $doc->getXML();
    
    $this->assertInstanceOf('\DOMDocument', $xml);
    $this->db->delete('test.xml');
    
  }
  
  /**
   * @depends testGetXML 
   */
  public function testGetContents()
  {
    $this->db->add('test.xml', '<test xml:id="root"></test>');
    $doc = new Document($this->db, 'test.xml');
    
    $xml = $doc->getXML();
    $node = $xml->createElement('append');
    $xml->getElementById('root')->appendChild($node);
    
    $contents = $doc->getContents();
    $this->assertXmlStringEqualsXmlString('<test xml:id="root"><append/></test>', $contents);
    $this->db->delete('test.xml');
    
  }
  
  public function testXpath()
  {
    $this->db->add('test-1.xml', '<root><test/><test/></root>');
    $this->db->add('test-2.xml', '<root><test/></root>');
    
    $doc = new Document($this->db, 'test-1.xml');
    
    $result = $doc->xpath('//test');
    
    $this->assertNotEmpty($result);
    
    $xml = simplexml_load_string("<root>$result</root>");
    
    $this->assertInstanceOf('\SimpleXmlElement', $xml);
    
    $this->assertEquals(2, count($xml->test));
    
    $this->db->delete('test-1.xml');
    $this->db->delete('test-2.xml');
  }
}