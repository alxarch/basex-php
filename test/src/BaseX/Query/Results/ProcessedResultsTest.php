<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Query\Results;

use BaseX\Query\Results\ProcessedResults;

class ProcessedResultsMock extends ProcessedResults
{
  public function processData($data, $type) {
    return "processed<$type>:$data";
  }
}

/**
 * Description of ProcessedResultsTest
 *
 * @author alxarch
 */
class ProcessedResultsTest extends \PHPUnit_Framework_TestCase
{
  /**
   *
   * @var \BaseX\Query\Results\ProcessedResults
   */
  protected $results;


  protected function setUp() {
    parent::setUp();
    $this->results = new ProcessedResultsMock();
  }
  
  public function testProcessing()
  {
    $this->results->addResult('data', 4);
    $this->assertEquals("processed<4>:data", $this->results[0]);
  }
}
