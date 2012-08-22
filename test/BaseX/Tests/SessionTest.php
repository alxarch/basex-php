<?php

namespace BaseX\Tests;

use BaseX\Session;

/**
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
  /**
   *
   * @var BaseX\Session
   */
  protected $session;
  
  protected function setUp()
  {
    $this->session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PASS);
  }
  
  public function testConstruct()
  {
    $this->assertInstanceOf('BaseX\Session', $this->session);
  }
  
  public function testExecute()
  {
    $info = $this->session->execute('INFO');
    $this->assertNotEmpty($info);
    $this->assertContains('HOST: '.BASEX_HOST, $info);
  }
  
  public function testQuery()
  {
    $q = $this->session->query('<test/>');
    $this->assertInstanceOf('BaseX\Query', $q);
  }
  
  /**
   * @depends testExecute
   */
  public function testCreate()
  {
    $db = 'test_db_' . time();
    $input = '<test>'.time().'</test>';
    
    $this->session->create($db, '');
    $this->assertContains($db, $this->session->execute('LIST'));
    $this->drop($db);
    
    $this->session->create($db, $input);
    $this->assertContains($db.'.xml', $this->session->execute('LIST '.$db));
    
    $result = $this->session->execute("XQUERY db:open('$db', '$db.xml')");
    
    $this->assertXmlStringEqualsXmlString($input, $result);
    $this->drop($db);
    
  }
  
  /**
   * @depends testCreate
   */
  public function testAdd()
  {
    $db = 'test_db_' . time();
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    
    $this->session->create($db);
    $this->session->execute("OPEN $db");
    
    $this->session->add($path, $contents);
    
    $result = $this->session->execute("XQUERY db:open('$db', '$path')");
    
    $this->assertXmlStringEqualsXmlString($contents, $result);
    
    $this->drop($db);
    
  }
  
  /**
   * @depends testCreate
   */
  public function testReplace()
  {
    $db = 'test_db_' . time();
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    $replace = '<replace/>';
    
    $this->session->create($db);
    
    $this->session->add($path, $contents);
    
    $this->session->replace($path, $replace);
    
    $result = $this->session->execute("XQUERY db:open('$db', '$path')");
    
    $this->assertXmlStringEqualsXmlString($replace, $result);
    
    $this->drop($db);
  }
  
  /**
   * @depends testCreate
   */
  public function testStore()
  {
    $db = 'test_db_' . time();
    
    $path = 'raw.txt';
    
    $contents = 'raw';
    
    $this->session->create($db);
    
    $this->session->store($path, $contents);
    
    $this->assertContains($path, $this->session->execute('LIST '.$db));
    
    $this->session->execute('SET SERIALIZER method=raw');
    $result = $this->session->execute("XQUERY db:retrieve('$db', '$path')");
    
    $this->assertSame($result, $contents);
    
    $this->drop($db);
  }
  
  
  
  private function drop($db)
  {
    $this->session->execute('DROP DATABASE '.$db);
  }
}