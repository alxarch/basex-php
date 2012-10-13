<?php

namespace BaseX;

use BaseX\StreamWrapper;
use BaseX\PHPUnit\TestCaseDb;
use BaseX\Session\Socket;

class StreamWrapperTest extends TestCaseDb
{
  public function setUp()
  {
    parent::setUp();
    StreamWrapper::register($this->session);
//    $this->session->getSocket()->setTimeout();
  }
  
  /**
   * @expectedException PHPUnit_Framework_Error
   * @expectedExceptionMessage stream_wrapper_register(): Protocol basex:// is already defined.
   * 
   */
  public function testRegister()
  {
    StreamWrapper::register($this->session);
  }
  
  public function testOpen()
  {
    $wrapper = new StreamWrapper();
    
    $this->db->add('test.xml', '<test/>');
    $path = '';
    $this->assertTrue($wrapper->stream_open("basex://$this->dbname/test.xml", 'r', STREAM_REPORT_ERRORS, $path));
    $this->session->getSocket()->read();
    $this->session->getSocket()->clearBuffer();
    $this->session->unlock();
    
    $this->assertTrue($wrapper->stream_open("basex://$this->dbname/test.xml", 'w', STREAM_REPORT_ERRORS, $path));
    $this->session->getSocket()->read();
    $this->session->getSocket()->clearBuffer();
    $this->session->unlock();
  }
  
  public function testUnRegister()
  {
    $success = StreamWrapper::unregister();
    $this->assertTrue($success);
    StreamWrapper::register($this->session);
    
  }
  
  public function testOpenWrite()
  {
    $this->db->add('test.xml', '<test/>');
    $url = 'basex://'.$this->dbname.'/test.xml';
    
    $resource = fopen($url, 'w');
    $this->assertTrue(is_resource($resource));
    return $resource;
  }
  
  /**
   * @depends testOpenWrite
   */
  public function testCloseRWrite($resource)
  {
    $this->assertTrue(fclose($resource));
  }
  
  public function testWrite()
  {
   
    $url = 'basex://'.$this->dbname.'/test.xml';
    
    $resource = fopen($url, 'w');
    $this->assertTrue(is_resource($resource));
    
    fwrite($resource, '<test/>');
    fclose($resource);
    
    $this->assertContains('test.xml', $this->ls());
    $this->assertEquals('<test/>', $this->doc('test.xml'));
  }
  
  public function testOpenRead()
  {
    $this->db->add('test.xml', '<test/>');
    $url = 'basex://'.$this->dbname.'/test.xml';
    
    $resource = fopen($url, 'r');
    $this->assertTrue(is_resource($resource));
    return $resource;
  }
  
  /**
   * @depends testOpenRead
   */
  public function testCloseRread($resource)
  {
    $this->assertTrue(fclose($resource));
  }
 
  public function testOpenMode()
  {
    $this->db->add('test.xml', '<test/>');
    $url = 'basex://'.$this->dbname.'/test.xml';
    
//    $this->assertOpenFails($url, 'r');
    $this->assertOpenFails($url, 'r+');
//    $this->assertOpenFails($url, 'w');
    $this->assertOpenFails($url, 'w+');
    $this->assertOpenFails($url, 'a');
    $this->assertOpenFails($url, 'a+');
    $this->assertOpenFails($url, 'c');
    $this->assertOpenFails($url, 'c+');
    $this->assertOpenFails($url, 'x');
    $this->assertOpenFails($url, 'x+');
    
  }

  public function testOpenNotFound()
  {
    
    $url = 'basex://'.$this->dbname.'/nothere.xml';
    
    $this->assertOpenFails($url);
  }
  
  public function testOpenNoDb()
  {
    $url = 'basex://test.xml';
    $this->assertOpenFails($url);
    $this->assertOpenFails($url, 'w');
  }
  
  public function testOpenNoDocument()
  {
    $url = 'basex://'.$this->dbname.'/';
    
    $this->assertOpenFails($url, 'w');
    $this->assertOpenFails($url);
  }
  
  public function testRead()
  { 
    $this->db->add('test.xml', '<test/>');
    $url = 'basex://'.$this->dbname.'/test.xml';
    
    
    $handle = fopen($url, 'r');
    $test = fread($handle, 256);
    $this->assertEquals($test, '<test/>');
    
    fclose($handle);
    
    $this->assertFalse($this->session->isLocked());
    
  }
  
  public function testReadRaw()
  {
    
    $filename = DATADIR.'/test.jpg';
    $jpg = fopen($filename, 'r');
    
    $tmp = fread($jpg, 900);
    
    fclose($jpg);
    $this->db->store('test.jpg', $tmp);
    
    $url = 'basex://'.$this->dbname.'/test.jpg';
    $basex = fopen($url, 'r');
    
    $expect = substr($tmp, 0, 256);
    $actual = fread($basex, 256);

    $this->assertEquals(256, strlen($actual));
    $this->assertEquals($expect, $actual);

    fclose($basex);
  }
  
  public function testWriteReplace()
  {
    $this->db->add('test.xml', '<test/>');
    
    $url = 'basex://'.$this->dbname.'/test.xml';
    
    $handle = fopen($url, 'w');
    
    $size = fwrite($handle, '<new/>');
    
    fclose($handle);
    
    $this->assertEquals($size, strlen('<new/>'));
    
    $contents = $this->doc('test.xml');
    $this->assertEquals('<new/>', $contents);
    $this->assertXmlStringEqualsXmlString('<new/>', $contents);
  }
  
  public function testWriteAdd()
  {
    $url = 'basex://'.$this->dbname.'/test.xml';
    
    $handle = fopen($url, 'w');
    
    $size = fwrite($handle, '<test/>');
    
    fclose($handle);
    
    $this->assertEquals($size, strlen('<test/>'));
    
    $contents = $this->doc('test.xml');
    $this->assertEquals('<test/>', $contents);
    $this->assertXmlStringEqualsXmlString('<test/>', $contents);
  }
  
  public function testWriteStore()
  {
    $filename = DATADIR.'/test.jpg';
    $jpg = fopen($filename, 'r');
    
    $tmp = fread($jpg, 900);
    
    fclose($jpg);

    
    $url = 'basex://'.$this->dbname.'/test.jpg';
    
     
    $basex = fopen($url, 'w');
    
    fwrite($basex, $tmp);
    fclose($basex);
    
    $contents = $this->raw('test.jpg');
    
    $this->assertEquals($tmp, $contents);
  }
  
  public function testStreamCopy()
  {
    $filename = DATADIR.'/test.jpg';
    
    $jpg = fopen($filename, 'r');
    
    $basex = fopen("basex://$this->dbname/test.jpg", 'w');
    
    $ok = stream_copy_to_stream($jpg, $basex);
    
    $this->assertTrue($ok !== false);
    $this->assertTrue($ok > 0);
    
    fclose($basex);
    fclose($jpg);
    
    $contents = $this->raw('test.jpg');
    
    $this->assertEquals(file_get_contents($filename), $contents);
  }
  
  public function testEOF()
  {
    $tmp = fopen('php://temp', 'r+');
    
    $this->db->store('test.txt', 'test');
    
    $basex = fopen("basex://$this->dbname/test.txt", 'r');
    
    $total = 0;
    $times = 0;
    while(!feof($basex))
    {
      $total += fwrite($tmp, fread($basex, Socket::BUFFER_SIZE));
      $times++;
    }
    
    $this->assertEquals(1, $times);
    $this->assertTrue($total > 0);
    
    rewind($tmp);
    $this->assertEquals('test', stream_get_contents($tmp));
    
    fclose($basex);
    fclose($tmp);
    
  }
  
  public function testStat()
  {
    $this->db->add('test.xml', '<test/>');
    
    $url = "basex://$this->dbname/test.xml";
    
    $handle = fopen($url, 'r');
    
    $result = fstat($handle);
    
    $this->assertTrue(is_array($result));
 
    $keys = array(
      'dev',
      'ino',
      'mode' ,
      'nlink',
      'uid' ,
      'gid' ,
      'rdev' ,
      'size' ,
      'atime' ,
      'mtime' ,
      'ctime' ,
      'blksize' ,
      'blocks'
    );
    foreach ($keys as $key)
    {
      $this->assertTrue(array_key_exists($key, $result));
    }
    
    $this->assertEquals(0100000+0444, $result['mode']);
    
    fclose($handle);
    $handle = fopen($url, 'w');
    $result = fstat($handle);
    $this->assertTrue(is_array($result));
    foreach ($keys as $key)
    {
      $this->assertTrue(array_key_exists($key, $result));
    }
    $this->assertEquals(0100000+0666, $result['mode']);
    
  }
//  
  protected function assertOpenFails($url, $mode='r', $msg='Resource did not fail on open.')
  {
    try
    {
      fopen($url, $mode);
    }
    catch (\PHPUnit_Framework_Error $e)
    {
      $needle = 'failed to open stream';
      $this->assertContains($needle, $e->getMessage());
      return;
    }
    
    throw new \PHPUnit_Framework_AssertionFailedError($msg);
    
  }
  
  public function testStartSending()
  {
    $wrapper = new StreamWrapper();
    
    $path = '';
    $this->assertTrue($wrapper->stream_open("basex://$this->dbname/test.xml", 'w', STREAM_REPORT_ERRORS, $path));
    $wrapper->startSending();
    $wrapper->stream_write('<test/>');
    $wrapper->stopSending();
    
    $this->assertContains('test.xml', $this->ls());
    $this->assertXmlStringEqualsXmlString('<test/>', $this->doc('test.xml'));
   
  }


  public function tearDown()
  {
    $this->session->unlock();
    parent::tearDown();
    StreamWrapper::unregister();
  }
}
