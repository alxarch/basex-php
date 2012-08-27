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
    self::$db->store('test.txt', 'test');
    try
    {
      $doc = new Document(self::$db, 'test.txt');
    }
    catch (InvalidArgumentException $e)
    {
      self::$db->delete('text.txt');
      throw $e;
    }
  }
  
  public function testGetXML()
  {
    self::$db->add('test.xml', '<test/>');
    $doc = new Document(self::$db, 'test.xml');
    
    $xml = $doc->getXML();
    
    $this->assertInstanceOf('\DOMDocument', $xml);
    self::$db->delete('test.xml');
    
  }
  
  /**
   * @depends testGetXML 
   */
  public function testGetContents()
  {
    self::$db->add('test.xml', '<test xml:id="root"></test>');
    $doc = new Document(self::$db, 'test.xml');
    
    $xml = $doc->getXML();
    $node = $xml->createElement('append');
    $xml->getElementById('root')->appendChild($node);
    
    $contents = $doc->getContents();
    $this->assertXmlStringEqualsXmlString('<test xml:id="root"><append/></test>', $contents);
    self::$db->delete('test.xml');
    
  }
  
  public function testXpath()
  {
    self::$db->add('test-1.xml', '<root><test/><test/></root>');
    self::$db->add('test-2.xml', '<root><test/></root>');
    
    $doc = new Document(self::$db, 'test-1.xml');
    
    $result = $doc->xpath('//test');
    
    $this->assertNotEmpty($result);
    
    $xml = simplexml_load_string("<root>$result</root>");
    
    $this->assertInstanceOf('\SimpleXmlElement', $xml);
    
    $this->assertEquals(2, count($xml->test));
    
    self::$db->delete('test-1.xml');
    self::$db->delete('test-2.xml');
  }
}