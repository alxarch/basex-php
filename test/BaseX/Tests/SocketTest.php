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
  
  static protected $server = null;
  
  static protected $conn = null;
  static protected $port = null;

  static public function setUpBeforeClass()
  {
    //Test socket on pseudo server.
    $port = rand(10000, 12000);
    self::$port = $port;
    self::$server = stream_socket_server("tcp://localhost:$port");
//    if(false === self::$server)
//      throw new \Exception('No server possible.');
//    if(false === self::$conn)
//      throw new \Exception('No connection possible.');
    
    self::$socket = new Socket('localhost', $port);
    self::$conn = stream_socket_accept(self::$server);
  }
  
  public function testConstruct()
  {
    $this->assertInstanceOf('BaseX\Session\Socket', self::$socket);
  }
  
  public function testRead()
  {
    $msg = sprintf('time is: %d', time());
    fwrite(self::$conn, $msg.chr(0));
    $result = self::$socket->read();
    $this->assertNotEmpty($result);
    $this->assertNotContains(chr(0), $msg);
    $this->assertEquals($msg, $result);
  }
  
  public function testWrite()
  {
    $msg = 'INFO';
    $expect = strlen($msg);
    $size = self::$socket->send($msg);
    $result = fread(self::$conn, $expect);
    $this->assertFalse(false === $size);
    $this->assertEquals($size, $expect);
    $this->assertEquals($msg, $result);
    
    $filename = __DIR__.'/../../data/test.html';
    $file = fopen($filename, 'r');
    $expect = filesize($filename);
    $size = self::$socket->send($file);
    $this->assertFalse(false === $size);
    $this->assertEquals($expect, $size);
  }
  
  public function testClose()
  {
    $this->assertTrue(self::$socket->close());
    
    // recreate client/server connection.
    fclose(self::$conn);
    self::$socket = new Socket('localhost', self::$port);
    self::$conn = stream_socket_accept(self::$server);
  }
  
  static public function tearDownAfterClass()
  {
    if(null !== self::$socket)
      self::$socket->close();
    
    fclose(self::$conn);
    fclose(self::$server);
  }
  
}
