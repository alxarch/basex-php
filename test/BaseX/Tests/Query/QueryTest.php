<?php

namespace BaseX\Tests\Query;

use BaseX\Query;
use BaseX\PHPUnit\TestCaseSession;

class QueryTest extends TestCaseSession
{
  private function query($xq)
  {
    return new Query($this->session, $xq);
  }

  public function testGetId()
  {
    $q1 = new Query($this->session, '<test1/>');
    $this->assertInternalType('integer', $q1->getId());
    $q2 = new Query($this->session, '<test2/>');
    $this->assertInternalType('integer', $q2->getId());
    
    $this->assertNotEquals($q1->getId(), $q2->getId());
  }
 
  public function testExecute()
  {
    $expected = '<root/>';
    $actual = $this->session->query($expected)->execute();
    $this->assertNotEmpty($actual);
    $this->assertXmlStringEqualsXmlString($expected, $actual);
  }
  
  public function testGetResults()
  {
    $results = $this->query('<root/>')->getResults();
    
    $this->assertTrue(is_array($results));
    
    $this->assertEquals(1, count($results));
    $result = $results[0];
    $this->assertInstanceOf('BaseX\Query\QueryResultInterface', $result);
    $this->assertEquals('<root/>', $result->getData());
    $this->assertEquals(11, $result->getType());
  }
}