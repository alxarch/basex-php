<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Query\Results;

use BaseX\Query;
use BaseX\Query\Results\SimpleXMLResults;
use PHPUnit_Framework_TestCase;

/**
 * Description of SimpleXMLResultTest
 *
 * @author alxarch
 */
class SimpleXMLResultsTest extends PHPUnit_Framework_TestCase
{
  
  /**
   *
   * @var SimpleXMLResults
   */
  protected $results;
  
  public function setUp() {
    $this->results = new SimpleXMLResults();
  }
  
  function testAddResult()
  {
    $this->results->addResult('<root/>', null);
    $this->assertInstanceOf('SimpleXMLElement', $this->results[0]);
    $this->assertXmlStringEqualsXmlString('<root/>', $this->results[0]->asXML());
  }
 
  function testSetNonXMLData()
  {
    $this->results->addResult('test', 242);
    $this->assertNull($this->results[0]);
  }
  
  function testSupportsType()
  {
    $this->assertFalse($this->results->supportsType(Query::TYPE_ANYTYPE));
    $this->assertTrue($this->results->supportsType(Query::TYPE_NODE));
    $this->assertTrue($this->results->supportsType(Query::TYPE_ELEMENT));
    $this->assertTrue($this->results->supportsType(Query::TYPE_DOCUMENT));
    $this->assertTrue($this->results->supportsType(Query::TYPE_DOCUMENT_ELEMENT));
   
  }
}

