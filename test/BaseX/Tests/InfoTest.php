<?php
namespace BaseX\Tests;


use \PHPUnit_Framework_TestCase as TestCase;
use BaseX\Document\Info;

class InfoTest extends TestCase
{
  protected $raw = '<resource raw="true" content-type="image/jpeg" modified-date="2012-05-27T12:36:48.000Z" size="60751">image.jpg</resource>';
  
  protected $xml = '<resource raw="false" content-type="application/xml" modified-date="2012-05-27T13:38:33.988Z">collection/doc.xml</resource>';
  
  
  public function setUp()
  {
    $this->raw = new Info($this->raw);
    
    $this->xml = new Info($this->xml);
  }
  
  public function testSize()
  {
    $this->assertNull($this->xml->size());
    $this->assertNotNull($this->raw->size());
    $this->assertEquals('60751', $this->raw->size());
  }
  
  public function testRaw()
  {
    $this->assertTrue($this->raw->raw());
    $this->assertFalse($this->xml->raw());
  }
  public function testType()
  {
    $this->assertEquals('image/jpeg', $this->raw->type());
    $this->assertEquals('application/xml', $this->xml->type());
  }
  
  public function testModified()
  {
    $this->assertEquals('2012-05-27T12:36:48.000Z', $this->raw->modified());
    $this->assertEquals('2012-05-27T13:38:33.988Z', $this->xml->modified());
  }
 
  public function testPath()
  {
    $this->assertEquals('image.jpg', $this->raw->path());
    $this->assertEquals('collection/doc.xml', $this->xml->path());
  }
}