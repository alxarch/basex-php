<?php
/**
 * @package BaseX
 * @subpackage Tests
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\PHPUnit;

use BaseX\Session;
use PHPUnit_Framework_TestCase;

/**
 * TestCase for tests that require a BaseX session.
 * 
 * It builds/destroys a BaseX session before/after each test.
 * 
 * @package BaseX
 * @subpackage Tests
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