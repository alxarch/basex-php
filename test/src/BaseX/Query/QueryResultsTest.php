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
    $this->assertTrue(isset($this->results[0]));
    $this->assertEquals('data', $this->results[0]);
  }
  
  public function testCountable()
  {
    $this->results->addResult('data', 4);
    $this->assertTrue(isset($this->results[0]));
    $this->assertEquals('data', $this->results[0]);
    
    $this->assertEquals(1, count($this->results));
    $this->results->addResult('data', 4);
    $this->assertEquals(2, count($this->results));
  }
  
   
  public function testIterator()
  {
    $this->results->addResult('data', 4);
    $this->results->addResult('data', 4);
    
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

}
