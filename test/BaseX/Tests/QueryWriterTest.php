<?php

namespace BaseX\Tests;

use \PHPUnit_Framework_TestCase as TestCase;

use BaseX\Session;
use BaseX\Query\Writer;
use BaseX\Query;

class QueryWriterTest extends TestCase
{
  
  public function testGetParameter()
  {
    $w = new Writer('<test/>', array(), array(), array('test' => 3));
    $this->assertEquals($w->getParameter('test'), 3);
    $this->assertNull($w->getParameter('undefined'));
  }
  
  public function testGetParameters()
  {
    $params = array('test' => 3);
    $w = new Writer('<test/>', array(), array(), $params);
    $result = $w->getParameters();
    $this->assertTrue(is_array($result));
    $this->assertEquals($result, $params);
  }
  
  /**
   * @depends testGetParameters 
   */
  public function testSetParameters()
  {
    $params = array('test' => 3);
    $w = new Writer('<test/>', array(), array(), $params);
    $extra = array('test'=>8, 'other' => 'voidsalt');
    $result = $w->setParameters($extra);
    $this->assertInstanceOf('BaseX\Query\Writer', $result);
    $this->assertTrue($result === $w);
    $this->assertEquals($w->getParameters(), array('test' => 8, 'other' => 'voidsalt'));
  }
  
  /**
   * @depends testGetParameter 
   */
  public function testSetParameter()
  {
    $params = array('test' => 3);
    $w = new Writer('<test/>', array(), array(), $params);
    $result = $w->setParameter('test', 8);
    $this->assertInstanceOf('BaseX\Query\Writer', $result);
    $this->assertTrue($result === $w);
    $this->assertEquals($w->getParameter('test'), 8);
  }
  
    
  public function testGetOption()
  {
    $w = new Writer('<test/>', array(), array('test' => 3), array());
    $this->assertEquals($w->getOption('test'), 3);
    $this->assertNull($w->getOption('undefined'));
  }
  
  public function testGetOptions()
  {
    $opts = array('test' => 3);
    $w = new Writer('<test/>', array(),  $opts, array());
    $result = $w->getOptions();
    $this->assertTrue(is_array($result));
    $this->assertEquals($result, $opts);
  }
  
  /**
   * @depends testGetOptions 
   */
  public function testSetOptions()
  {
    $opts = array('test' => 3);
    $w = new Writer('<test/>', array(), $opts, array());
    $extra = array('test'=>8, 'other' => 'voidsalt');
    $result = $w->setOptions($extra);
    $this->assertInstanceOf('BaseX\Query\Writer', $result);
    $this->assertTrue($result === $w);
    $this->assertEquals($w->getOptions(), array('test' => 8, 'other' => 'voidsalt'));
  }
  
  /**
   * @depends testGetOption 
   */
  public function testSetOption()
  {
    $opts = array('test' => 3);
    $w = new Writer('<test/>', array(), $opts, array());
    $result = $w->setOption('test', 8);
    $this->assertInstanceOf('BaseX\Query\Writer', $result);
    $this->assertTrue($result === $w);
    $this->assertEquals($w->getOption('test'), 8);
  }
  
    
    
  public function testGetVariable()
  {
    $w = new Writer('<test/>', array('test' => 3), array(), array());
    $this->assertEquals($w->getVariable('test'), 3);
    $this->assertNull($w->getVariable('undefined'));
  }
  
  public function testGetVariables()
  {
    $vars = array('test' => 3);
    $w = new Writer('<test/>',  $vars, array(), array());
    $result = $w->getVariables();
    $this->assertTrue(is_array($result));
    $this->assertEquals($result, $vars);
  }
  
  /**
   * @depends testGetVariables 
   */
  public function testSetVariables()
  {
    $vars = array('test' => 3);
    $w = new Writer('<test/>', $vars, array(), array());
    $extra = array('test'=>8, 'other' => 'voidsalt');
    $result = $w->setVariables($extra);
    $this->assertInstanceOf('BaseX\Query\Writer', $result);
    $this->assertTrue($result === $w);
    $this->assertEquals($w->getVariables(), array('test' => 8, 'other' => 'voidsalt'));
  }
  
  /**
   * @depends testGetVariable 
   */
  public function testSetVariable()
  {
    $vars = array('test' => 3);
    $w = new Writer('<test/>', $vars, array(), array());
    $result = $w->setVariable('test', 8);
    $this->assertInstanceOf('BaseX\Query\Writer', $result);
    $this->assertTrue($result === $w);
    $this->assertEquals($w->getVariable('test'), 8);
  }
  
  public function testGetBody()
  {
    $w = new Writer('<body/>');
    $this->assertEquals($w->getBody(), '<body/>');
  }
  
  /**
   * @depends testGetBody 
   */
  public function testSetBody()
  {
    $w = new Writer('<body/>');
    $w->setBody('<new/>');
    $this->assertEquals($w->getBody(), '<new/>');
  }
  
  /**
   * @depends testBuild 
   */
  public function testGetQuery()
  {
    $session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PASS);
    
    $w = new Writer('<body/>');
    $q = $w->getQuery($session);
    $this->assertInstanceOf('BaseX\Query', $q);
    $this->assertTrue($session === $q->getSession());
    
    $vars = array('contents' => "Hello World!");
    $opts = array('chop', 'false');
    $params = array('method', 'xml');
    $w = new Writer('<body>{$contents}</body>', $vars, $opts, $params);
    
    $q =  $w->getQuery($session);
    
    $this->assertInstanceOf('BaseX\Query', $q);
    $this->assertTrue($session === $q->getSession());
    $this->assertXmlStringEqualsXmlString('<body>Hello World!</body>', $q->execute());
  }
  public function testBuild()
  {
    $vars = array('contents' => "Hello World!");
    $opts = array('chop', 'false');
    $params = array('method', 'xml');
    $w = new Writer('<body>{$contents}</body>', $vars, $opts, $params);
    
    $expect = <<<XQL
declare variable external \$contents;
declare option db:chop = "false";
declare option output:method = "xml";
<body>{\$contents}</body>
XQL;
    
    $this->assertEquals($w->build(), $expect);
  }
}