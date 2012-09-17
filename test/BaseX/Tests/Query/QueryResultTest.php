<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Tests\Query;

use PHPUnit_Framework_TestCase as TestCase;

use BaseX\Query\QueryResult;

/**
 * Description of QueryResultTest
 *
 * @author alxarch
 */
class QueryResultTest extends TestCase
{
  function testGetSupportedTypes()
  {
    $types = array(
        7,8,9,10,11,12,13,14,15,
        32,33,34,35,36,37,38,39,
        40,41,42,43,44,45,46,47,48,49,
        50,51,52,53,54,55,56,57,58,59,
        60,61,62,63,64,65,66,67,68,69,
        70,71,72,73,74,75,76,77,78,79,
        80,81,82,83
    );
    
    $this->assertEquals($types, QueryResult::getSupportedTypes());
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  function testGetType()
  {
    $test = new QueryResult();
    $test->setType(100);
  }
}

