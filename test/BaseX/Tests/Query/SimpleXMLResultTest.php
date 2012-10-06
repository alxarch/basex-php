<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Tests\Query;

use PHPUnit_Framework_TestCase as TestCase;
use BaseX\Query\SimpleXMLResult;

/**
 * Description of SimpleXMLResultTest
 *
 * @author alxarch
 */
class SimpleXMLResultTest extends TestCase
{
  
  function testSetData()
  {
    $data = '<root/>';
    
    $test = new SimpleXMLResult();
    $this->assertInstanceOf('BaseX\Query\SimpleXMLResult', $test->setData($data));
    
    $this->assertXmlStringEqualsXmlString($data, $test->getData());
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  function testSetNonXMLData()
  {
    $data = 'error';
    
    $test = new SimpleXMLResult();
    $test->setData($data);
  }
  
   function testSetType()
  {
    $data = '<root/>';
    
    $test = new SimpleXMLResult();
    $test->setData($data);
    $this->assertInstanceOf('BaseX\Query\SimpleXMLResult', $test->setType(11));
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  function testSetNonValidType()
  {
    $type = 100;
    
    $test = new SimpleXMLResult();
    $test->setType($type);
  }
  
  function testGetData()
  {
    $data=<<<XML
      <root attr="value">
        <items>
          <item/>
          <item/>
        </items>
        <test>test</test>
        <int>3</int>
        <bool>true</bool>
        <f>false</f>
        <float>3.2</float>
        <t/>
        <t/>
        <t/>
      </root>
XML;
    $test = new SimpleXMLResult();
    $test->setData($data);
    $this->assertXmlStringEqualsXmlString($data, $test->getData());
    $this->assertFalse(strpos($test->getData(), '<?xml version="1.0"?>') === 0);
  }
  
  function testGetXml()
  {
    $data = '<root/>';
    
    $test = new SimpleXMLResult();
    $test->setData($data);
    
    $this->assertInstanceOf('SimpleXMLElement', $test->getXML());
    $this->assertXmlStringEqualsXmlString($data, $test->getXML()->asXML());
  }
  
  function test__get()
  {
    $data=<<<XML
      <root attr="value">
        <items>
          <item/>
          <item/>
        </items>
        <test>test</test>
        <int>3</int>
        <bool>true</bool>
        <f>false</f>
        <float>3.2</float>
        <t/>
        <t/>
        <t/>
      </root>
XML;
    
    $result = new SimpleXMLResult();
    
    $result->setData($data);
    
    $this->assertEquals('value', $result['attr']);
    $this->assertEquals(3, $result->int);
    $this->assertTrue(3.2 === $result->float);
    $this->assertTrue(false === $result->f);
    $this->assertTrue(true === $result->bool);
    $this->assertInstanceOf('\SimpleXMLElement', $result->items);
    $this->assertInstanceOf('\SimpleXMLElement', $result->t);
  }
  
  public function testSerialize()
  {
    $data=<<<XML
      <root attr="value">
        <items>
          <item/>
          <item/>
        </items>
        <test>test</test>
        <int>3</int>
        <bool>true</bool>
        <f>false</f>
        <float>3.2</float>
        <t/>
        <t/>
        <t/>
      </root>
XML;
    
    $result = new SimpleXMLResult();
    
    $result->setData($data);
    
    $serialized = serialize($result);
    $after = unserialize($serialized);
    
    $this->assertXmlStringEqualsXmlString($data, $after->getXML()->asXML());
    
  }
}

