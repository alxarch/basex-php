<?php

namespace BaseX\Tests;

use BaseX\Session\Socket;
use BaseX\Helpers as B;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * 
 */
class SocketTest extends \PHPUnit_Framework_TestCase
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
    fwrite(self::$conn, "$msg\x00");
    $result = self::$socket->read();
    $this->assertEquals($msg, $result);
    
    $msg = "___\x00___\x00\x00";
    fwrite(self::$conn, "$msg");
    $result = self::$socket->read();
    $this->assertEquals('___', $result);
    $result = self::$socket->read();
    $this->assertEquals('___', $result);
    $result = self::$socket->read();
    $this->assertEquals('', $result);
    
  }
  
  public function testReadInto()
  {
    $tmp = fopen('php://memory', 'r+');
    
    $data = str_repeat('#', 512);
    
    fwrite(self::$conn, "$data\x00");
    
    $actual = self::$socket->readInto($tmp);
    $this->assertEquals(strlen($data), $actual);
    rewind($tmp);
    $actual = stream_get_contents($tmp);
    $this->assertEquals($data, $actual);
  }
  
  public function testReadSingle()
  {
    fwrite(self::$conn, 'abcd');
    $this->assertEquals('a', self::$socket->readSingle());
    $this->assertEquals('b', self::$socket->readSingle());
    $this->assertEquals('c', self::$socket->readSingle());
    $this->assertEquals('d', self::$socket->readSingle());
  }
  
  public function testReadWithPadAtEndOfBuffer()
  {
    $msg = str_repeat('_', 4*Socket::BUFFER_SIZE - 1)."\xFF\x00";
    
    fwrite(self::$conn, "$msg\x00");
    
    $expect = str_repeat('_', 4*Socket::BUFFER_SIZE - 1)."\x00";
    
    $actual = self::$socket->read();
    
    $this->assertEquals($expect, $actual);
    
  }
  public function testReadUnScrubbing()
  {

    $msg = "___\xFF\x00\x00___\x00";
    $expect = "___\x00";
    fwrite(self::$conn, $msg);
    $actual = self::$socket->read();
    $this->assertEquals($expect, $actual);
    $actual = self::$socket->read();
    $expect = "___";
    $this->assertEquals($expect, $actual);
  }
  
  public function testSend()
  {
    $msg = 'INFO';
    $expect = strlen($msg);
    $size = self::$socket->send($msg);
    $result = fread(self::$conn, $expect);
    $this->assertFalse(false === $size);
    $this->assertEquals($size, $expect);
    $this->assertEquals($msg, $result);
    
    $data = str_repeat('#', 1024);
    $tmp = fopen('php://memory', 'r+');
    $size =fwrite($tmp, $data);
    rewind($tmp);
    $actual = self::$socket->send($tmp);
    $this->assertEquals($size, $actual);
    
    rewind($tmp);
    $expect = stream_get_contents($tmp);
    $actual = fread(self::$conn, $size);
    
    $this->assertEquals($expect, $actual);
    
    fclose($tmp);
  }
  
  public function testGetBuffer()
  {
    $expect = str_repeat('_', Socket::BUFFER_SIZE);
    fwrite(self::$conn, $expect);
    $actual = self::$socket->getBuffer();
    $this->assertEquals($actual, $expect);
    
    $data = str_repeat('#', Socket::BUFFER_SIZE);
    fwrite(self::$conn, $data);
    $actual = self::$socket->getBuffer();
    $this->assertEquals($actual, $expect);
    
    return $data;

  }
  
  /**
   * @depends testGetBuffer 
   */
  public function testClearBuffer($expect)
  {
    self::$socket->clearBuffer();
    $actual = self::$socket->getBuffer();
    $this->assertEquals($actual, $expect);
    
    fwrite(self::$conn, 'wrong');
    self::$socket->clearBuffer();
    self::$socket->getBuffer();
    fwrite(self::$conn, 'right');
    self::$socket->clearBuffer();
    $actual = self::$socket->getBuffer();
    $this->assertEquals('right', $actual);
  }
  
  public function testSetTimeout()
  {
    $this->assertTrue(self::$socket->setTimeout(0,10));
    self::$socket->clearBuffer();
    self::$socket->getBuffer();
  }
  
  public function testDetectErrorCommandProtocol()
  {
    $data = "Begining of data...\x00Error message.\x00\x01";
    fwrite(self::$conn, $data);
    self::$socket->clearBuffer();
    $actual = self::$socket->detectError();
    
    $this->assertEquals($actual, 'Error message.');
  }
  
  public function testDetectErrorCommandProtocolNoError()
  {
    $data = "Begining of data...\x00Status message.\x00\x00";
    fwrite(self::$conn, $data);
    self::$socket->clearBuffer();
    $actual = self::$socket->detectError();
    
    $this->assertFalse($actual);
  }
  
  public function testDetectErrorQueryCommandProtocol()
  {
    $data = "Begining of data...\x00\x01Error message.\x00";
    fwrite(self::$conn, $data);
    self::$socket->clearBuffer();
    $actual = self::$socket->detectError();
    $this->assertEquals($actual, 'Error message.');
  }
  
  public function testDetectErrorQueryCommandProtocolNoError()
  {
    $data = "Begining of data...\x00Success message.\x00";
    fwrite(self::$conn, $data);
    self::$socket->clearBuffer();
    $this->assertFalse(self::$socket->detectError());
  }
  
  public function testStream()
  {
    $data = str_repeat('%', 512);
    
    fwrite(self::$conn, "$data\x00");
    self::$socket->clearBuffer();
    $actual = '';
    $size = self::$socket->stream(256, $actual);
    $this->assertEquals(256, $size);
    $expect = str_repeat('%', 256);
    $this->assertEquals($expect, $actual);
    
    $size = self::$socket->stream(512, $actual);
    $this->assertEquals(256, $size);
    $this->assertEquals($expect, $actual);
    
    $data = "___\xFF\x00___\x00___\xFF\xFF___\x00___\x00";
    
    fwrite(self::$conn, $data);
    
    self::$socket->clearBuffer();
    $actual = '';
    $size = self::$socket->stream(256, $actual);
    $this->assertEquals(7, $size);
    $expect = "___\x00___";
    $this->assertEquals($expect, $actual);
    
    $size = self::$socket->stream(512, $actual);
    $this->assertEquals(7, $size);
    $expect = "___\xFF___";
    $this->assertEquals($expect, $actual);
    
    $size = self::$socket->stream(2, $actual);
    $this->assertEquals(2, $size);
    $expect = "__";
    $this->assertEquals($expect, $actual);
    
    $size = self::$socket->stream(200, $actual);
    $this->assertEquals(1, $size);
    $expect = "_";
    $this->assertEquals($expect, $actual);
    
    $filename = $filename = __DIR__.'/../../data/test.jpg';
    $jpg = fopen($filename, 'r');
    while(!feof($jpg))
    {
      $data = fread($jpg, 4096);
      fwrite(self::$conn, B::scrub($data));
    }
    fwrite(self::$conn, "\x00");
    rewind($jpg);
    
    $expect = fread($jpg, 256);
    self::$socket->clearBuffer();
    $size = self::$socket->stream(256, $actual);
    $this->assertEquals($expect, $actual);
    $this->assertEquals(256, $size);
    $equals = true;
    $sizeok = true;
    while(!feof($jpg))
    {
      $expect = fread($jpg, 4096);
      $size = self::$socket->stream(4096, $actual);
      if($expect !== $actual)
      {
        $equals = false;
        break;
      }
      
      if(!feof($jpg) && $size !== 4096)
      {
        $sizeok = false;
        break;
      }
        
    }
    $this->assertTrue($sizeok, 'Size wrong');
    $this->assertTrue($equals);
    
    fclose($jpg);
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
