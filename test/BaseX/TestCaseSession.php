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
  protected $session;
  
  /**
   *
   * @var string
   */
  protected $dbname;
  
  protected function setUp()
  {
    $this->session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PASS);
    $this->dbname = 'test_db_' . time();
  }
  
  protected function tearDown()
  {
    $this->session->close();
  }
}