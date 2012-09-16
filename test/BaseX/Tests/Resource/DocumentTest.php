<?php

namespace BaseX\Tests;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Session;
use BaseX\Database;
use BaseX\Resource\Document;

class DocumentTest extends TestCaseDb
{
  /**
   * @expectedException BaseX\Error
   * @expectedExceptionMessage Specified resource is not a document. 
   */
  public function testInit()
  {
    $this->db->store('test.txt', 'test');
    $doc = new Document($this->session, $this->dbname, 'test.txt');
  }
  
  public function testGetXML()
  {
    $this->db->add('test.xml', '<test/>');
    $doc = new Document($this->session, $this->dbname, 'test.xml');
    
    $xml = $doc->getXML();
    
    $this->assertInstanceOf('\DOMDocument', $xml);
    $this->db->delete('test.xml');
    
  }
  
//  /**
//   * @depends testGetXML 
//   */
//  public function testGetContents()
//  {
//    $this->db->add('test.xml', '<test xml:id="root"></test>');
//    $doc = new Document($this->session, $this->dbname, 'test.xml');
//    
//    $xml = $doc->getXML();
//    $node = $xml->createElement('append');
//    $xml->getElementById('root')->appendChild($node);
//    
//    $contents = $doc->getContents();
//    $this->assertXmlStringEqualsXmlString('<test xml:id="root"><append/></test>', $contents);
//    $this->db->delete('test.xml');
//    
//  }
  
  public function testXpath()
  {
    $this->db->add('test-1.xml', '<root><test/><test/></root>');
    $this->db->add('test-2.xml', '<root><test/></root>');
    
    $doc = new Document($this->session, $this->dbname, 'test-1.xml');
    
    $result = $doc->xpath('//test');
    
    $this->assertNotEmpty($result);
    
    $xml = simplexml_load_string("<root>$result</root>");
    
    $this->assertInstanceOf('\SimpleXMLElement', $xml);
    
    $this->assertEquals(2, count($xml->test));
    
    $this->db->delete('test-1.xml');
    $this->db->delete('test-2.xml');
  }
}