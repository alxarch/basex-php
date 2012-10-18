<?php

namespace BaseX;

use BaseX\Query;
use BaseX\PHPUnit\TestCaseSession;
use BaseX\Query\QueryResults;

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
  
  public function testOptions()
  {
    $q = $this->query('declare option output:omit-xml-declaration "false";<root/>');
    $opts = $q->options();
    $this->assertTrue(is_array($opts));
    $this->assertTrue(array_key_exists('omit-xml-declaration', $opts));
    $this->assertFalse($opts['omit-xml-declaration']);
  }
  
  public function testGetResults()
  {
    $results = $this->query('<root/>')->getResults();
    
    $this->assertTrue($results instanceof QueryResults);
    
    $this->assertEquals(1, count($results));
    $result = $results[0];
    $this->assertXmlStringEqualsXmlString('<root/>', $result);
  }
}