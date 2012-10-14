<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Query\Result;


use BaseX\PHPUnit\TestCaseSession as TestCase;
use BaseX\Query\Result\SimpleXMLMapper;
use BaseX\Query;

/**
 * Description of SimpleXMLResultTest
 *
 * @author alxarch
 */
class SimpleXMLMapperTest extends TestCase
{
  
  function testSetData()
  {
    $mapper = new SimpleXMLMapper();
    $data = '<root/>';
    
    $result = $mapper->getResult($data, Query::TYPE_ELEMENT);
    $this->assertInstanceOf('SimpleXMLElement', $result);
    $this->assertXmlStringEqualsXmlString($data, $result->asXML());
  }
  
  /**
   * @expectedException BaseX\Error\ResultMapperError
   */
  function testSetNonXMLData()
  {
    $data = 'error';
    
    $mapper = new SimpleXMLMapper();
    
    $mapper->getResult($data, Query::TYPE_ELEMENT);
  }
  
  function testSupportsType()
  {
    $mapper = new SimpleXMLMapper();
    $this->assertFalse($mapper->supportsType(Query::TYPE_ANYTYPE));
    $this->assertTrue($mapper->supportsType(Query::TYPE_NODE));
    $this->assertTrue($mapper->supportsType(Query::TYPE_ELEMENT));
    $this->assertTrue($mapper->supportsType(Query::TYPE_DOCUMENT));
    $this->assertTrue($mapper->supportsType(Query::TYPE_DOCUMENT_ELEMENT));
   
  }
}

