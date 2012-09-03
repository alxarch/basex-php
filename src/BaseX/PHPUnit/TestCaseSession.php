<?php

namespace BaseX\PHPUnit;

use BaseX\Session;
use \PHPUnit_Framework_TestCase;

/**
 */
class TestCaseSession extends PHPUnit_Framework_TestCase
{
  /**
   *
   * @var \BaseX\Session
   */
  protected $session;
  
  public function setUp()
  {
    $this->session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PASS);
  }
  
  public function tearDown()
  {
    $this->session->close();
  }
}