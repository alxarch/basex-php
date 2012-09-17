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
  
  function testGetXml()
  {
    $data = '<root/>';
    
    $test = new SimpleXMLResult();
    $test->setData($data);
    
    $this->assertInstanceOf('SimpleXMLElement', $test->getXML());
    $this->assertXmlStringEqualsXmlString($data, $test->getXML()->asXML());
  }
  
  
}

