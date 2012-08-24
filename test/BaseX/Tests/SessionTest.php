<?php

namespace BaseX\Tests;

use BaseX\Session;
use PHPUnit_Framework_TestCase as TestCase;

/**
 */
class SessionTest extends TestCase
{
  /**
   *
   * @var BaseX\Session
   */
  protected $session;
  
  protected $db;
  
  protected function setUp()
  {
    $this->session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER, BASEX_PASS);
    $this->db = 'test_db_' . time();
  }
  
  /**
   * @expectedException         BaseX\Session\Exception
   * @expectedExceptionMessage  Access denied.
   */
  public function testAuthenticate()
  {
    $session = new Session(BASEX_HOST, BASEX_PORT, BASEX_USER.time(), BASEX_PASS.time());
  }
  
  public function testConstruct()
  {
    $this->assertInstanceOf('BaseX\Session', $this->session);
  }
  
//  public function testGetVersion()
//  {
//    $this->assertRegExp('/\d+\.\d+/', $this->session->getVersion());
//  }
  
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
    $this->session->create($this->db);
    
    $this->assertContains($this->db, $this->session->execute('LIST'));
    
    $this->drop($this->db);
    
    $input = '<test>'.time().'</test>';
    
    $this->session->create($this->db, $input);
    
    $this->assertContains($this->db, $this->session->execute('LIST'));
    $this->assertContains($this->db.'.xml', $this->session->execute('LIST '.$this->db));
    
    $result = $this->session->execute("XQUERY db:open('$this->db', '$this->db.xml')");
    
    $this->assertXmlStringEqualsXmlString($input, $result);
    
    $this->drop($this->db);
    
  }
  
  /**
   * @depends testCreate
   */
  public function testAdd()
  {
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    
    $this->session->create($this->db);
    $this->session->execute("OPEN $this->db");
    
    $this->session->add($path, $contents);
    
    $result = $this->session->execute("XQUERY db:open('$this->db', '$path')");
    
    $this->assertXmlStringEqualsXmlString($contents, $result);
    
    $this->drop($this->db);
    
  }
  
  /**
   * @depends testCreate
   */
  public function testReplace()
  {
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    $replace = '<replace/>';
    
    $this->session->create($this->db);
    
    $this->session->add($path, $contents);
    
    $this->session->replace($path, $replace);
    
    $result = $this->session->execute("XQUERY db:open('$this->db', '$path')");
    
    $this->assertXmlStringEqualsXmlString($replace, $result);
    
    $this->drop($this->db);
  }
  
  /**
   * @depends testCreate
   */
  public function testStore()
  {
    
    $path = 'raw.txt';
    
    $contents = 'raw';
    
    $this->session->create($this->db);
    
    $this->session->store($path, $contents);
    
    $this->assertContains($path, $this->session->execute('LIST '.$this->db));
    
    $this->session->execute("SET SERIALIZER method=raw");
    $this->session->execute("OPEN $this->db");
    $result = $this->session->execute("RETRIEVE $path");
    
    $this->assertSame($result, $contents);
    
    $this->drop($this->db);
  }
  
  /**
   * @depends testExecute 
   */
  public function testScript()
  {
    $script = "CREATE DB $this->db;DROP DB $this->db";
    
    $this->session->script($script);
    
    $list = $this->session->execute("LIST");
    
    
    $script = "<commands><create-db name='$this->db'/><open name='$this->db'/></commands>";
    $this->session->script($script);
    $list = $this->session->execute("LIST");
    $this->assertContains($this->db, $list);
    $script = "<commands><close/><drop-db name='$this->db'/></commands>";
    $this->session->script($script);
    $list = $this->session->execute("LIST");
    $this->assertNotContains($this->db, $list);
    
  }
  
  private function drop($db)
  {
    $this->session->execute('DROP DATABASE '.$db);
  }
}