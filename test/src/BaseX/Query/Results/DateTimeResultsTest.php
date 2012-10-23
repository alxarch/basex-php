<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Query\Results;

use BaseX\Query\Results\DateTimeResults;
use DateTime;
use PHPUnit_Framework_TestCase;

/**
 * Description of DateTimeMapperTest
 *
 * @author alxarch
 */
class DateTimeResultsTest extends PHPUnit_Framework_TestCase
{
  /**
   *
   * @var DateTimeResults
   */
  protected $results;


  protected function setUp() 
  {
    parent::setUp();
    $this->results = new DateTimeResults();
  }
  
  
  function testProcessing() {
    $date = date('c');
    $this->results->addResult($date, 23);
    $r = $this->results[0];
    $this->assertTrue($r instanceof DateTime);
    $this->assertEquals($date, $r->format('c'));
    
  }
  function testFormat()
  {
    $this->results->setFormat('l, j F Y H:i');
    $this->results->addResult('Monday, 2 April 2012 12:35', 1);
    $this->assertEquals(date_create_from_format('l, j F Y H:i', 'Monday, 2 April 2012 12:35'), $this->results[0]);
    $this->assertEquals('Monday', $this->results[0]->format('l'));
  }
}

