<?php

namespace BaseX;

use BaseX\Session;
use BaseX\PHPUnit\TestCaseSession;

/**
 */
class SessionTest extends TestCaseSession
{
  /**
   * @expectedException         BaseX\Error\SessionError
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
    $this->assertTrue($q->getSession() === $this->session);
  }
  
  /*
   * @depends testExecute
   */
  public function testCreate()
  {
    $db = 'test_db_'.time();
    $result = $this->session->create($db);
    $this->assertEmpty($result);
    $list = $this->session->query('db:list()')->execute();
    $this->assertNotEmpty($this->session->getStatus());
    $this->assertContains($db, $list);
    
    $this->session->execute("DROP DB $db");
    
    $input = '<test>'.time().'</test>';
    $this->session->create($db, $input);
//    
    $list = $this->session->query('db:list()')->execute();
    $this->assertContains($db, $list);
//    
    $list = $this->session->query("db:list('$db')")->execute();
    $this->assertContains("$db.xml", $list);
    
    $result = $this->session->query("db:open('$db', '$db.xml')")->execute();
    
    $this->assertXmlStringEqualsXmlString($input, $result);
    
    $this->session->execute("OPEN $db");
    $this->session->execute("DELETE $db.xml");
    $this->session->execute("DROP DB $db");
  }
//  
  /**
   * @depends testCreate
   */
  public function testAdd()
  {
    $db = 'test_db_'.time();
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    
    $this->session->create($db);
    $this->session->execute("OPEN $db");
    
    $this->session->add($path, $contents);
    
    $result = $this->session->execute("XQUERY db:open('$db', '$path')");
    
    $this->assertXmlStringEqualsXmlString($contents, $result);
    
    $this->session->execute("DROP DB $db");
  }
  
  /**
   * @depends testCreate
   */
  public function testAddResource()
  {
    $db = 'test_db_'.time();
    $path = 'test.xml';
    $filename = __DIR__.'/../../data/test.xml';
    
    $input = fopen($filename, 'r');
    
    $this->session->create($db);
    $this->session->execute("OPEN $db");
    $this->session->add($path, $input);
    
    fclose($input);
    
    $this->assertContains($path, $this->session->execute("LIST $db"));
    
    $expect = file_get_contents($filename);
    $actual = $this->session->execute("XQUERY db:open('$db', '$path')");
    $this->assertXmlStringEqualsXmlString($expect, $actual); 
    
    $this->session->execute("DROP DB $db");
  }
  
  /**
   * @depends testCreate
   */
  public function testStoreResource()
  {
    $db = 'test_db_'.time();
    $path = 'test.jpg';
    $filename = DATADIR.'/test.jpg';
    
    $input = fopen($filename, 'r');
    
    $this->session->create($db);
    $this->session->execute("OPEN $db");
    $this->session->store($path, $input);
    fclose($input);
    
    $this->assertContains($path, $this->session->execute("LIST $db"));
    
    $this->session->execute("SET SERIALIZER raw");
    $actual = $this->session->execute("RETRIEVE $path");
    $expect = file_get_contents($filename);

    $this->assertEquals($expect, $actual); 
    
    $this->session->execute("SET SERIALIZER");
    $this->session->execute("DROP DB $db");
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
    
    $this->session->create($db);
    $this->session->execute("OPEN $db");
    
    $this->session->add($path, $contents);
    
    $this->session->replace($path, $replace);
    
    $result = $this->session->execute("XQUERY db:open('$db', '$path')");
    
    $this->assertXmlStringEqualsXmlString($replace, $result);
    
    $this->session->execute("DELETE $path");    
    $this->session->execute("DROP DB $db");
  }
  
  /**
   * @depends testCreate
   */
  public function testStore()
  {
    
    $db = 'test_db_'.time();
    $path = 'raw.txt';
    $contents = 'raw';
    
    $this->session->create($db);
    $this->session->execute("OPEN $db");
    $this->session->store($path, $contents);
    
    $list = $this->session->execute("LIST $db");
    $this->assertContains($path, $list);
    
    $this->session->execute("SET SERIALIZER method=raw");
    $this->session->execute("OPEN $db");
    $result = $this->session->execute("RETRIEVE $path");
    
    $this->assertSame($result, $contents);
    
    $this->session->execute("SET SERIALIZER");
    $this->session->execute("DROP DB $db");
  }
  
  /**
   * @depends testExecute 
   */
  public function testScript()
  {
    $db = 'script_test_db_'.time();
    $script = "CREATE DB $db;DROP DB $db";
    
    $this->session->script($script);
    
    $list = $this->session->execute("LIST");
    
    $this->assertNotContains($db, $list);
    
    $script = "<commands><create-db name='$db'/><open name='$db'/></commands>";
    
    $this->session->script($script);
    
    $list = $this->session->execute("LIST");
    $this->assertContains($db, $list);
    
    $script = "<commands><close/><drop-db name='$db'/></commands>";
    $this->session->script($script);
    
    $list = $this->session->execute("LIST");
    $this->assertNotContains($db, $list);
    
    $this->session->execute("DROP DB ".$db);
    
  }
  
  function testGetInfo() {
    $result = $this->session->getInfo();
    
    $this->assertInstanceOf('BaseX\Session\SessionInfo', $result);
  }
  
  function testGetStatus() 
  {
    $this->session->execute('INFO');
    $status = $this->session->getStatus();
    $this->assertEquals('', $status);
  }
  
  /**
   * @expectedException BaseX\Error\SessionError
   */
  function testLock()
  {
    $this->session->lock();
    $this->session->execute('INFO');
  }
  
  public function tearDown() {
    $this->session->unlock();
    parent::tearDown();
  }
}