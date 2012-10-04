<?php

namespace BaseX\Tests;

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
  }
  
  public function testMap()
  {
    $this->assertEquals("{}", B::map(array()));
    $this->assertEquals("{'a' := 'banana', 'b' := 'test'}", B::map(array(
        'a' => 'banana',
        'b' => 'test'
    )));
    
  }
      
}