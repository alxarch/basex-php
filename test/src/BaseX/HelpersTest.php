<?php

namespace BaseX;

use BaseX\Helpers as B;
use PHPUnit_Framework_TestCase as TestCase;

class HelpersTest  extends TestCase
{
  public function testEscape()
  {
    $string = "A string with \"quotes\" inside.";
    $expect = "A string with \\\"quotes\\\" inside.";
    $actual = B::escape($string);
    $this->assertEquals($expect, $actual);
    
//    $string = "A string with ascii zero(".chr(0).") character.";
//    $expect = "A string with ascii zero(".chr(255).chr(0).") character.";
//    $actual = H::escape($string);
//    $this->assertEquals($expect, $actual);
    
  }
  
  public function testOptions()
  {
    $options = array('test'=>1,'more'=>2);
    $expect = 'test=1,more=2';
    $actual = B::options($options);
    $this->assertEquals($expect, $actual);
    
    $expect = 'test=1;more=2';
    $actual = B::options($options, ";");
    $this->assertEquals($expect, $actual);
    
    $options = array('test'=>1);
    $expect = 'test=1';
    $actual = B::options($options);
    $this->assertEquals($expect, $actual);
  }
  
  public function testScrub()
  {
    $data =   "___\x00___\xFF___\xFF\x00___";
    $expect = "___\xFF\x00___\xFF\xFF___\xFF\xFF\xFF\x00___";
    $this->assertEquals($expect, B::scrub($data));
  }
  
  public function testUnScrub()
  {
    $expect = "___\x00___\xFF___\xFF\x00___";
    $data =   "___\xFF\x00___\xFF\xFF___\xFF\xFF\xFF\x00___";
    $this->assertEquals($expect, B::unscrub($data));
  }
  
  public function testUriSimple()
  {
    $this->assertEquals("basex://database/path.xml", B::uri('database', 'path.xml'));
  }
  
  
  public function testCamelize()
  {
    $this->assertEquals('GetMe', B::camelize('get_me'));
    $this->assertEquals('GetMe', B::camelize('Get_Me'));
    $this->assertEquals('GetMeIfYouCan', B::camelize('get_me_if_you_can'));
  }
  
  public function testValue()
  {
    $this->assertEquals('()', B::value(null));
    $this->assertEquals('(1,2)', B::value(array(1,2)));
    $this->assertEquals("('a','b')", B::value(array('a','b')));
    $this->assertEquals('true()', B::value(true));
    $this->assertEquals('false()', B::value(false));
    $this->assertEquals("'test'", B::value('test'));
    $this->assertEquals("map {'a' := 'banana', 'b' := 'test'}", B::value(array(
        'a' => 'banana',
        'b' => 'test'
    )));
  }
  
  public function testMap()
  {
    $this->assertEquals("{}", B::map(array()));
    $this->assertEquals("{'a' := 'banana', 'b' := 'test'}", B::map(array(
        'a' => 'banana',
        'b' => 'test'
    )));
    $this->assertEquals("{'a' := 'banana', 2 := 'test'}", B::map(array(
        'a' => 'banana',
        2 => 'test'
    )));
    
  }
  
  public function testURI()
  {
    $this->assertEquals('basex://db/path/to.xml', B::uri('db', 'path/to.xml'));
    $this->assertEquals('basex://db/path/to.xml?serializer=method%3Djson#html', B::uri('db', 'path/to.xml', 'html', array('serializer'=>'method=json')));
    $this->assertEquals('basex://db/path/to.xml?htmlopt=method%3Dxhtml#html', B::uri('db', 'path/to.xml', 'html', array('htmlopt'=>array('method'=>'xhtml'))));
    $this->assertEquals('basex://db/path/to.xml?parseopt=lines%3Dtrue#html', B::uri('db', 'path/to.xml', 'html', array('parseopt'=>array('lines'=>'true'))));
  }
  
  public function testDate()
  {
    $date = new \DateTime();
    
    $this->assertEquals($date, B::date($date->format('Y-m-d\TH:i:s.u\Z')));
  }
  
  public function testStripXML()
  {
    $string = '<?xml version="1.0"?><root/>';
   $this->assertEquals('<root/>', B::stripXMLDeclaration($string));
  }
      
  function testRename(){
    $this->assertEquals('test/renamed.txt', B::rename('test/test.xml', 'renamed.txt'));
    $this->assertEquals('renamed.txt', B::rename('test.xml', 'renamed.txt'));
  }
  function testDirname(){
    $this->assertEquals('test', B::dirname('test/test.xml'));
    $this->assertEquals('', B::dirname('test.xml'));
    
  }
  
  function testRelative(){
    $this->assertEquals('test.xml', B::relative('test/test.xml', 'test'));
    $this->assertEquals('test/test.xml', B::relative('/test/test.xml', ''));
    $this->assertFalse(B::relative('sa/test.xml', 'test'));
    
  }
  
  function testPath(){
    $this->assertEquals('test/renamed.txt', B::path('test', 'renamed.txt'));
    $this->assertEquals('test/sa/renamed.txt', B::path('test', 'sa', 'renamed.txt'));
    $this->assertEquals('test/sa/renamed.txt', B::path('/test', 'sa', 'renamed.txt'));
    $this->assertEquals('test/sa/renamed.txt', B::path('test', 'sa', 'renamed.txt/'));
    $this->assertEquals('test/sa/renamed.txt', B::path('test', '/sa', 'renamed.txt/'));
    $this->assertEquals('renamed.txt', B::path('renamed.txt'));
  }
  
  function testConvert()
  {
    $this->assertTrue(B::convert('true'));
    $this->assertFalse(B::convert('false'));
    $this->assertInternalType('integer', B::convert('123'));
    $this->assertInternalType('float', B::convert('123.32'));
    $this->assertEquals('so long', B::convert('so long'));
  }
}