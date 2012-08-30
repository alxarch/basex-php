<?php

namespace BaseX\Tests;

use BaseX\Session;
use BaseX\Session\Info;
use BaseX\TestCaseSession;

/**
 */
class SessionTest extends TestCaseSession
{
  /**
   * @expectedException         BaseX\Session\Exception
   * @expectedExceptionMessage  Access denied.
   */
  public function testAuthenticate()
  {
    $pass = BASEX_PASS . time();
    $user = BASEX_USER.time();
    $session = new Session(BASEX_HOST, BASEX_PORT, $user, $pass);
  }
  
  public function testConstruct()
  {
    $this->assertInstanceOf('BaseX\Session', self::$session);
  }
  
//  public function testGetVersion()
//  {
//    $this->assertRegExp('/\d+\.\d+/', self::$session->getVersion());
//  }
  
  public function testExecute()
  {
    $info = self::$session->execute('INFO');
    $this->assertNotEmpty($info);
    $this->assertContains('HOST: '.BASEX_HOST, $info);
  }
  
  public function testQuery()
  {
    $q = self::$session->query('<test/>');
    $this->assertInstanceOf('BaseX\Query', $q);
    $this->assertTrue($q->getSession() === self::$session);
  }
  
  /**
   * @depends testExecute
   */
  public function testCreate()
  {
    $db = 'test_db_'.time();
    self::$session->create($db);
    
    $list = self::$session->execute('LIST');
    $this->assertContains($db, $list);
    
    self::$session->execute("DROP DB $db");
    
    $input = '<test>'.time().'</test>';
    
    self::$session->create($db, $input);
    
    $list = self::$session->execute('LIST');
    $this->assertContains($db, $list);
    
    $list = self::$session->execute("LIST $db");
    $this->assertContains($db.'.xml', $list);
    
    $result = self::$session->execute("XQUERY db:open('$db', '$db.xml')");
    
    $this->assertXmlStringEqualsXmlString($input, $result);
    
    self::$session->execute("OPEN $db");
    self::$session->execute("DELETE $db.xml");
    self::$session->execute("DROP DB $db");
  }
  
  /**
   * @depends testCreate
   */
  public function testAdd($db)
  {
    $db = 'test_db_'.time();
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    
    self::$session->create($db);
    self::$session->execute("OPEN $db");
    
    self::$session->add($path, $contents);
    
    $result = self::$session->execute("XQUERY db:open('$db', '$path')");
    
    $this->assertXmlStringEqualsXmlString($contents, $result);
    
    self::$session->execute("DELETE $path");
    self::$session->execute("DROP DB $db");
  }
  
  /**
   * @depends testAdd
   */
  public function testReplace()
  {
    
    $db = 'test_db_'.time();
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    $replace = '<replace/>';
    
    self::$session->create($db);
    self::$session->execute("OPEN $db");
    
    self::$session->add($path, $contents);
    
    self::$session->replace($path, $replace);
    
    $result = self::$session->execute("XQUERY db:open('$db', '$path')");
    
    $this->assertXmlStringEqualsXmlString($replace, $result);
    
    self::$session->execute("DELETE $path");    
    self::$session->execute("DROP DB $db");
  }
  
  /**
   * @depends testCreate
   */
  public function testStore()
  {
    
    $db = 'test_db_'.time();
    $path = 'raw.txt';
    $contents = 'raw';
    
    self::$session->create($db);
    self::$session->execute("OPEN $db");
    self::$session->store($path, $contents);
    
    $list = self::$session->execute("LIST $db");
    $this->assertContains($path, $list);
    
    self::$session->execute("SET SERIALIZER method=raw");
    self::$session->execute("OPEN $db");
    $result = self::$session->execute("RETRIEVE $path");
    
    $this->assertSame($result, $contents);
    
    self::$session->execute("DELETE $path");
    self::$session->execute("DROP DB $db");
  }
  
  /**
   * @depends testExecute 
   */
  public function testScript()
  {
    $db = 'script_test_db_'.time();
    $script = "CREATE DB $db;DROP DB $db";
    
    self::$session->script($script);
    
    $list = self::$session->execute("LIST");
    
    $this->assertNotContains($db, $list);
    
    $script = "<commands><create-db name='$db'/><open name='$db'/></commands>";
    
    self::$session->script($script);
    
    $list = self::$session->execute("LIST");
    $this->assertContains($db, $list);
    
    $script = "<commands><close/><drop-db name='$db'/></commands>";
    self::$session->script($script);
    
    $list = self::$session->execute("LIST");
    $this->assertNotContains($db, $list);
    
    self::$session->execute("DROP DB ".$db);
    
  }
  
  function testGetInfo() {
    $result = self::$session->getInfo();
    
    $this->assertInstanceOf('BaseX\Session\Info', $result);
  }
  
  function testGetStatus() 
  {
    self::$session->execute('INFO');
    $status = self::$session->getStatus();
    $this->assertEquals(sprintf('%c%c', 0, 0), $status);
  }
}