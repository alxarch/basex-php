<?php

namespace BaseX\Query;

use BaseX\Query\QueryBuilder;
use BaseX\Session;
use PHPUnit_Framework_TestCase as TestCase;

class QueryBuilderTest extends TestCase
{

  public function testSetNamespace()
  {
    $w = QueryBuilder::begin()->setBody('<test/>');
    $result = $w->setNamespace('tei', 'http://www.tei-c.org/ns/1.0');
    $this->assertInstanceOf('BaseX\Query\QueryBuilder', $result);
    $this->assertTrue($result === $w);
    return $w;
  }
  
  public function testSetNamespaces()
  {
    $w = QueryBuilder::begin()->setBody('<test/>');
    $namespaces = array(
      'tei' => 'http://www.tei-c.org/ns/1.0', 
      'other' => 'http://example.com'
    );
    
    $result = $w->setNamespaces($namespaces);
    $this->assertInstanceOf('BaseX\Query\QueryBuilder', $result);
    $this->assertTrue($result === $w);
    
    return array($w, $namespaces);
  }
  
  /**
   * @depends testSetNamespaces 
   */
  public function testGetNamespaces($args)
  {
    list($w, $namespaces) = $args;
    $result = $w->getNamespaces();
    $this->assertTrue(is_array($result));
    $this->assertEquals($result, $namespaces);
  }
  
  

  public function testSetModules()
  {
    $w = QueryBuilder::begin()->setBody('<test/>');
    $mods = array('functx'=> 'http://www.functx.com');
    $result = $w->setModules($mods);
    $this->assertInstanceOf('BaseX\Query\QueryBuilder', $result);
    $this->assertTrue($result === $w);
    
    return array($w, $mods);
  }
  
  /**
   * @depends testSetModules 
   */
  public function testGetModules($args)
  {
    list($w, $mods) = $args;
    $result = $w->getModules();
    $this->assertTrue(is_array($result));
    $this->assertEquals($result, $mods);
  }

  public function testSetModule()
  {
    $w = QueryBuilder::begin()->setBody('<test/>');
    $result = $w->setModule('functx', 'http://www.functx.com');
    $this->assertInstanceOf('BaseX\Query\QueryBuilder', $result);
    $this->assertTrue($result === $w);
    return $w;
  }

  public function testSetParameters()
  {
    $w = QueryBuilder::begin()->setBody('<test/>');
    $params = array('test' => 8, 'other' => 'something');
    $result = $w->setParameters($params);
    $this->assertInstanceOf('BaseX\Query\QueryBuilder', $result);
    $this->assertTrue($result === $w);
    
    return array($w, $params);
  }
  
  /**
   * @depends testSetParameters 
   */
  public function testGetParameters($args)
  {
    list($w, $params) = $args;
    $result = $w->getParameters();
    $this->assertTrue(is_array($result));
    $this->assertEquals($result, $params);
  }

  public function testSetParameter()
  {
    $w = QueryBuilder::begin()->setBody('<test/>');
    $result = $w->setParameter('test', 3);
    $this->assertInstanceOf('BaseX\Query\QueryBuilder', $result);
    $this->assertTrue($result === $w);
    return $w;
  }
  
  /**
   * @depends testSetParameter 
   */
  public function testGetParameter(QueryBuilder $w)
  {
    $this->assertEquals($w->getParameter('test'), 3);
    $this->assertNull($w->getParameter('undefined'));
    
  }
  
  public function testSetOptions()
  {
    $w = QueryBuilder::begin()->setBody('<test/>');
    $opts = array('test' => 3, 'other' => 'voidsalt');
    $result = $w->setOptions($opts);
    $this->assertInstanceOf('BaseX\Query\QueryBuilder', $result);
    $this->assertTrue($result === $w);
    
    return array($w, $opts);
  }
  
  /**
   * @depends testSetOptions 
   */
  public function testGetOptions($args)
  {
    list($w, $options) = $args;
    $result = $w->getOptions();
    $this->assertTrue(is_array($result));
    $this->assertEquals($result, $options);
  }
  
  public function testSetOption()
  {
    $w = QueryBuilder::begin()->setBody('<test/>');
    $result = $w->setOption('test', 3);
    $this->assertInstanceOf('BaseX\Query\QueryBuilder', $result);
    $this->assertTrue($result === $w);
    
    return $w;
  }
  
  /**
   * @depends testSetOption 
   */
  public function testGetOption(QueryBuilder $w)
  {
    $this->assertEquals($w->getOption('test'), 3);
    $this->assertNull($w->getOption('undefined'));
  }
  
  public function testGetBody()
  {
    $w = QueryBuilder::begin()->setBody('<body/>');
    $this->assertEquals($w->getBody(), '<body/>');
  }
  
  /**
   * @depends testGetBody 
   */
  public function testSetBody()
  {
    $w = QueryBuilder::begin()->setBody('<body/>');
    $w->setBody('<new/>');
    $this->assertEquals($w->getBody(), '<new/>');
  }
  public function testBuild()
  {
    $w = QueryBuilder::begin();
    $w->setBody('<body>{$contents}</body>');
    
    $opts = array('chop' => 'false');
    $params = array('method' => 'xml');
    $namespaces = array('tei' => 'http://www.tei-c.org/ns/1.0');
    $modules = array('functx'=> 'http://www.functx.com');
    
    $w->setOptions($opts)
      ->setParameters($params)
      ->setNamespaces($namespaces)
      ->setModules($modules);
    
    $expect = implode("\n", array(
      "declare namespace tei = 'http://www.tei-c.org/ns/1.0';",
      "import module namespace functx = 'http://www.functx.com';",
      "declare option output:method 'xml';",
      "declare option db:chop 'false';",
      "<body>{\$contents}</body>",
    ));
    
    $this->assertEquals($expect, $w->build());
  }
  
  /**
   * @depends testBuild 
   */
  public function testGetQuery()
  {
    $session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PASS);
    
    $w = QueryBuilder::begin()->setBody('<body/>');
    $q = $w->getQuery($session);
    $this->assertInstanceOf('BaseX\Query', $q);
    $this->assertTrue($session === $q->getSession());
    
    $opts = array('chop' => 'false');
    $params = array('method' => 'xml');
    $w = QueryBuilder::begin()
            ->setBody('<body>{"Hello World!"}</body>')
            ->setOptions($opts)
            ->setParameters($params);
    
    $q =  $w->getQuery($session);
    $this->assertInstanceOf('BaseX\Query', $q);
    $this->assertTrue($session === $q->getSession());
    $this->assertXmlStringEqualsXmlString('<body>Hello World!</body>', $q->execute());
  }
  
}