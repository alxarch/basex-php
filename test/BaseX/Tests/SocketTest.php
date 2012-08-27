<?php

namespace BaseX\Tests;

use BaseX\Session\Socket;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * 
 */
class SocketTest extends TestCase
{
  /**
   *
   * @var BaseX\Session\Socket
   */
  static protected $socket = null;
  
  static public function setUpBeforeClass()
  {
    self::$socket = new Socket(BASEX_HOST, BASEX_PORT);
  }
  
  public function testConstruct()
  {
    $this->assertInstanceOf('BaseX\Session\Socket', self::$socket);
  }
  
  public function testRead()
  {
    $ts = self::$socket->read();
    $this->assertNotEmpty($ts);
    $this->assertNotContains(chr(0), $ts);
    $this->assertRegExp('/^\d+$/', $ts);
  }
  
  public function testWrite()
  {
    $error = false;
    try{
      self::$socket->send('INFO');
    }
    catch(\Exception $e)
    {
      $error = true;
    }
    
    $this->assertFalse($error);
    
  }
  
  public function testClose()
  {
    $this->assertTrue(self::$socket->close());
    
    self::$socket = new Socket(BASEX_HOST, BASEX_PORT);
  }
  
  
  static public function tearDownAfterClass()
  {
    if(null !== self::$socket)
      self::$socket->close();
  }
  
}
