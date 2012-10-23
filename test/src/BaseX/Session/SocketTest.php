<?php

namespace BaseX\Session;

use BaseX\Helpers as B;
use BaseX\Session\Socket;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * 
 */
class SocketTest extends TestCase
{
  /**
   *
   * @var Socket
   */
  protected $socket = null;
  
  protected $server = null;
  
  protected $conn = null;
  protected $port = null;

  public function setUp()
  {
    //Test socket on pseudo server.
    $port = rand(10000, 12000);
    $this->port = $port;
    $this->server = stream_socket_server("tcp://localhost:$port");
   
    $this->socket = new Socket('localhost', $port);
    $this->conn = stream_socket_accept($this->server);
  }
  
  public function testConstruct()
  {
    $this->assertInstanceOf('BaseX\Session\Socket', $this->socket);
  }
  
  public function testRead()
  {
    $msg = sprintf('time is: %d', time());
    fwrite($this->conn, "$msg\x00");
    $result = $this->socket->read();
    $this->assertEquals($msg, $result);
    
    $msg = "___\x00___\x00\x00";
    fwrite($this->conn, "$msg");
    $result = $this->socket->read();
    $this->assertEquals('___', $result);
    $result = $this->socket->read();
    $this->assertEquals('___', $result);
    $result = $this->socket->read();
    $this->assertEquals('', $result);
    
  }
  
  public function testReadInto()
  {
    $tmp = fopen('php://memory', 'r+');
    
    $data = str_repeat('#', 512);
    
    fwrite($this->conn, "$data\x00");
    
    $actual = $this->socket->readInto($tmp);
    $this->assertEquals(strlen($data), $actual);
    rewind($tmp);
    $actual = stream_get_contents($tmp);
    $this->assertEquals($data, $actual);
  }
  
  public function testReadSingle()
  {
    fwrite($this->conn, 'abcd');
    $this->assertEquals('a', $this->socket->readSingle());
    $this->assertEquals('b', $this->socket->readSingle());
    $this->assertEquals('c', $this->socket->readSingle());
    $this->assertEquals('d', $this->socket->readSingle());
  }
  
  public function testReadWithPadAtEndOfBuffer()
  {
    $msg = str_repeat('_', 4*Socket::BUFFER_SIZE - 1)."\xFF\x00";
    
    fwrite($this->conn, "$msg\x00");
    
    $expect = str_repeat('_', 4*Socket::BUFFER_SIZE - 1)."\x00";
    
    $actual = $this->socket->read();
    
    $this->assertEquals($expect, $actual);
    
  }
  public function testReadUnScrubbing()
  {

    $msg = "___\xFF\x00\x00___\x00";
    $expect = "___\x00";
    fwrite($this->conn, $msg);
    $actual = $this->socket->read();
    $this->assertEquals($expect, $actual);
    $actual = $this->socket->read();
    $expect = "___";
    $this->assertEquals($expect, $actual);
  }
  
  public function testSend()
  {
    $msg = 'INFO';
    $expect = strlen($msg);
    $size = $this->socket->send($msg);
    $result = fread($this->conn, $expect);
    $this->assertFalse(false === $size);
    $this->assertEquals($size, $expect);
    $this->assertEquals($msg, $result);
    
    $data = str_repeat('#', 1024);
    $tmp = fopen('php://memory', 'r+');
    $size =fwrite($tmp, $data);
    rewind($tmp);
    $actual = $this->socket->send($tmp);
    $this->assertEquals($size, $actual);
    
    rewind($tmp);
    $expect = stream_get_contents($tmp);
    $actual = fread($this->conn, $size);
    
    $this->assertEquals($expect, $actual);
    
    fclose($tmp);
  }
  
  public function testGetBuffer()
  {
    $expect = str_repeat('_', Socket::BUFFER_SIZE);
    fwrite($this->conn, $expect);
    $actual = $this->socket->getBuffer();
    $this->assertEquals($actual, $expect);
    
    $data = str_repeat('#', Socket::BUFFER_SIZE);
    fwrite($this->conn, $data);
    $actual = $this->socket->getBuffer();
    $this->assertEquals($actual, $expect);


  }

  public function testClearBuffer()
  {
    $expect = str_repeat('#', Socket::BUFFER_SIZE);
    fwrite($this->conn, $expect);
    $this->socket->clearBuffer();
    $actual = $this->socket->getBuffer();
    $this->assertEquals($expect, $actual);
    
    fwrite($this->conn, 'wrong');
    $this->socket->clearBuffer();
    $this->socket->getBuffer();
    fwrite($this->conn, 'right');
    $this->socket->clearBuffer();
    $actual = $this->socket->getBuffer();
    $this->assertEquals('right', $actual);
  }
  
  public function testSetTimeout()
  {
    $this->assertTrue($this->socket->setTimeout(0,10));
    $this->socket->clearBuffer();
    $this->socket->getBuffer();
  }
  
  public function testDetectErrorCommandProtocol()
  {
    $data = "Begining of data...\x00Error message.\x00\x01";
    fwrite($this->conn, $data);
    $this->socket->clearBuffer();
    $actual = $this->socket->detectError();
    
    $this->assertEquals($actual, 'Error message.');
  }
  
  public function testDetectErrorCommandProtocolNoError()
  {
    $data = "Begining of data...\x00Status message.\x00\x00";
    fwrite($this->conn, $data);
    $this->socket->clearBuffer();
    $actual = $this->socket->detectError();
    
    $this->assertFalse($actual);
  }
  
  public function testDetectErrorQueryCommandProtocol()
  {
    $data = "Begining of data...\x00\x01Error message.\x00";
    fwrite($this->conn, $data);
    $this->socket->clearBuffer();
    $actual = $this->socket->detectError();
    $this->assertEquals($actual, 'Error message.');
  }
  
  public function testDetectErrorQueryCommandProtocolNoError()
  {
    $data = "Begining of data...\x00Success message.\x00";
    fwrite($this->conn, $data);
    $this->socket->clearBuffer();
    $this->assertFalse($this->socket->detectError());
  }
  
  public function testStream()
  {
    $data = str_repeat('%', 512);
    
    fwrite($this->conn, "$data\x00");
    $this->socket->clearBuffer();
    $actual = '';
    $size = $this->socket->stream(256, $actual);
    $this->assertEquals(256, $size);
    $expect = str_repeat('%', 256);
    $this->assertEquals($expect, $actual);
    
    $size = $this->socket->stream(512, $actual);
    $this->assertEquals(256, $size);
    $this->assertEquals($expect, $actual);
    
    $data = "___\xFF\x00___\x00___\xFF\xFF___\x00___\x00";
    
    fwrite($this->conn, $data);
    
    $this->socket->clearBuffer();
    $actual = '';
    $size = $this->socket->stream(256, $actual);
    $this->assertEquals(7, $size);
    $expect = "___\x00___";
    $this->assertEquals($expect, $actual);
    
    $size = $this->socket->stream(512, $actual);
    $this->assertEquals(7, $size);
    $expect = "___\xFF___";
    $this->assertEquals($expect, $actual);
    
    $size = $this->socket->stream(2, $actual);
    $this->assertEquals(2, $size);
    $expect = "__";
    $this->assertEquals($expect, $actual);
    
    $size = $this->socket->stream(200, $actual);
    $this->assertEquals(1, $size);
    $expect = "_";
    $this->assertEquals($expect, $actual);
    
    $filename = $filename = DATADIR.'/test.jpg';
    $jpg = fopen($filename, 'r');
    $times = 2;
    while(!feof($jpg) && $times--)
    {
      $data = fread($jpg, Socket::BUFFER_SIZE);
      fwrite($this->conn, B::scrub($data));
    }
    fwrite($this->conn, "\x00");
    rewind($jpg);
    
    $expect = fread($jpg, 256);
    $this->socket->clearBuffer();
    $size = $this->socket->stream(256, $actual);
    $this->assertEquals($expect, $actual);
    $this->assertEquals(256, $size);
    $equals = true;
    $sizeok = true;
    while(!feof($jpg))
    {
      $expect = fread($jpg, Socket::BUFFER_SIZE);
      $tmp = '';
      $size = $this->socket->stream(Socket::BUFFER_SIZE, $tmp);
      if($expect !== $tmp)
      {
        $equals = false;
        break;
      }
      
      if(!feof($jpg) && $size !== Socket::BUFFER_SIZE)
      {
        $sizeok = false;
        break;
      }
      
      break;
    }
    $this->assertTrue($sizeok, 'Size wrong');
    $this->assertTrue($equals);
    
    fclose($jpg);
  }
  
  public function testClose()
  {
    $this->assertTrue($this->socket->close());
    
    // recreate client/server connection.
    fclose($this->conn);
    $this->socket = new Socket('localhost', $this->port);
    $this->conn = stream_socket_accept($this->server);
  }
  
  public function tearDown()
  {
    if(null !== $this->socket)
      $this->socket->close();
    
    fclose($this->conn);
    fclose($this->server);
  }
  
}
