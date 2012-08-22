<?php

namespace BaseX\Tests;

use BaseX\Session;
use BaseX\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
   /**
   *
   * @var BaseX\Session
   */
  protected $session;
  
  protected function setUp()
  {
    $this->session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PASS);
  }
  protected function tearDown()
  {
    $this->session->close();
  }
  
  private function query($xq)
  {
    return new Query($this->session, $xq);
  }

  public function testInit()
  {
    $q = $this->query('<test/>');
    return $q;
  }
  
  /**
   * @depends testInit 
   */
  public function testGetId(Query $q)
  {
    $this->assertInternalType('integer', $q->getId());
//    $q2 = $this->query('<test2/>');
//    $this->assertNotEquals($q->getId(), $q2->getId());
  }
  
  /**
   * @depends testInit 
   */
  public function testExecute(Query $q)
  {
    $expected = '<root/>';
    $q = $this->query($expected);
    $actual = $q->execute();
    $this->assertNotEmpty($actual);
    $this->assertXmlStringEqualsXmlString($expected, $actual);
  }
}