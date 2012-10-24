<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Query;

use BaseX\Query\QueryResults;
use PHPUnit_Framework_TestCase;

/**
 * Description of QueryResultsTest
 *
 * @author alxarch
 */
class QueryResultsTest extends PHPUnit_Framework_TestCase {

  /**
   *
   * @var QueryResults
   */
  protected $results;

  public function setUp() {
    $this->results = new QueryResults();
  }

  public function testAddResult() {
    $this->results->addResult('data', 4);
  }

  public function testArrayAccess() {
    $this->results->addResult('data', 4);
    $this->assertTrue($this->results->count() > 0);
    $this->assertTrue($this->results->getIterator()->offsetGet(0) === 'data');
  }
  
  public function testCountable()
  {
    $this->results->addResult('data1', 4);
    $this->assertEquals('data1', $this->results->getFirst());
    
    $this->assertEquals(1, $this->results->count());
    $this->results->addResult('data2', 4);
    $this->assertEquals(2, $this->results->count());
  }
   
  public function testIterator()
  {
    $this->results->addResult('data', 4);
    $this->results->addResult('data', 4);
    $this->assertEquals(2, $this->results->count());
    foreach ($this->results as $result)
    {
      $this->assertEquals('data', $result);
    }
  
  }
   
  public function testGetFirstLast()
  {
    $this->results->addResult('first', 4);
    $this->results->addResult('data', 4);
    $this->results->addResult('last', 4);
    
    $this->assertEquals('first', $this->results->getFirst());
    $this->assertEquals('last', $this->results->getLast());
    $this->results->addResult('lastest', 4);
    $this->assertEquals('lastest', $this->results->getLast());
  
  }
  public function testGetSingle()
  {
    $this->results->addResult('data', 4);
    $this->assertEquals('data', $this->results->getSingle());
    
    $this->results->addResult('data', 4);
    
    $this->assertNull($this->results->getSingle());
  
  }
  
  function testJson()
  {
    $this->results->addResult('{"test": 12}', null);
    
    $this->assertEquals(json_decode('{"test": 12}'), $this->results->parseJSON()->getSingle());
    
  }
  
  function testCSV()
  {
    $this->results->addResult('"test", 12', null);
    
    $this->assertEquals(str_getcsv('"test", 12'), $this->results->parseCSV()->getSingle());
    
  }

  function testDateTime()
  {
    $time = time();
    $this->results->addResult($time, null);
    $dt = \DateTime::createFromFormat('u', $time);
    $this->assertEquals($dt, $this->results->parseDateTime('u')->getSingle());
    
  }
}
