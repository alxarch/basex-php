<?php

namespace BaseX\Tests;

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
    
  public function testGetContents()
  {
    $this->db->add('original.xml', '<test/>');
    
    $original = new Document($this->session, $this->dbname, 'original.xml');
    
    $this->assertXmlStringEqualsXmlString('<test/>', $original->getContents());
  }
  
  public function testGetContentsInto()
  {
    $into = fopen('php://temp', 'r+');
    
    $this->db->add('original.xml', '<test/>');
    
    $original = new Document($this->session, $this->dbname, 'original.xml');
    
    $result = $original->getContents($into);
    
    $this->assertFalse(false === $result);
    $this->assertTrue(is_int($result));
    $this->assertTrue($result > 0);
    
    rewind($into);
    
    $contents = stream_get_contents($into);
    
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
    
    fclose($into);
  }
    
  public function testCopy()
  {
    $this->db->add('original.xml', '<test/>');
    
    $original = new Document($this->session, $this->dbname, 'original.xml');
    
    $copy = $original->copy('copy.xml');
    
    $this->assertInstanceOf('BaseX\Resource\Document', $copy);
    $this->assertEquals('copy.xml', $copy->getPath());
    
    $this->assertContains('copy.xml', $this->ls());
    $this->assertContains('original.xml', $this->ls());
    
    $this->assertEquals('<test/>', $this->doc('copy.xml'));
  }
  
  function tearDown()
  {
    StreamWrapper::unregister();
    parent::tearDown();
  }
}