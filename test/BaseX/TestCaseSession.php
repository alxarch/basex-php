<?php

namespace BaseX;

use BaseX\Session;
use \PHPUnit_Framework_TestCase;

/**
 */
class TestCaseSession extends PHPUnit_Framework_TestCase
{
  /**
   *
   * @var BaseX\Session
   */
  static protected $session;
  
  static public function setUpBeforeClass()
  {
    self::$session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PASS);
  }
  
  static public function tearDownAfterClass()
  {
    self::$session->close();
  }
}