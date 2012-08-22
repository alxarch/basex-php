<?php


namespace BaseX\Tests;


use BaseX\SocketWrapper;

/**
 * 
 */
class SocketWrapperTest extends \PHPUnit_Framework_TestCase
{
  /**
   *
   * @var BaseX\SocketClient
   */
  protected $socket = null;
  
  protected function setUp()
  {
    $this->socket = new SocketWrapper(BASEX_HOST, BASEX_PORT);
  }
  
  public function testConstruct()
  {
    $this->assertInstanceOf('BaseX\SocketWrapper', $this->socket);
  }
  
  public function testRead()
  {
    $ts = $this->socket->read();
    $this->assertNotEmpty($ts);
    $this->assertNotContains(chr(0), $ts);
    $this->assertRegExp('/^\d+$/', $ts);
  }
  
  public function testWrite()
  {
    $error = false;
    try{
      $this->socket->send('INFO');
    }
    catch(\Exception $e)
    {
      $error = true;
    }
    
    $this->assertFalse($error);
    
  }
  
  protected function tearDown()
  {
    if(null !== $this->socket)
      $this->socket->close();
  }
  
}