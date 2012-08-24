<?php

namespace BaseX\Tests;

use BaseX\Session;
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
    $this->session->create($this->dbname);
    
    $this->assertContains($this->dbname, $this->session->execute('LIST'));
    
    $this->drop($this->dbname);
    
    $input = '<test>'.time().'</test>';
    
    $this->session->create($this->dbname, $input);
    
    $this->assertContains($this->dbname, $this->session->execute('LIST'));
    $this->assertContains($this->dbname.'.xml', $this->session->execute('LIST '.$this->dbname));
    
    $result = $this->session->execute("XQUERY db:open('$this->dbname', '$this->dbname.xml')");
    
    $this->assertXmlStringEqualsXmlString($input, $result);
    
    $this->drop($this->dbname);
    
  }
  
  /**
   * @depends testCreate
   */
  public function testAdd()
  {
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    
    $this->session->create($this->dbname);
    $this->session->execute("OPEN $this->dbname");
    
    $this->session->add($path, $contents);
    
    $result = $this->session->execute("XQUERY db:open('$this->dbname', '$path')");
    
    $this->assertXmlStringEqualsXmlString($contents, $result);
    
    $this->drop($this->dbname);
    
  }
  
  /**
   * @depends testCreate
   */
  public function testReplace()
  {
    $path = 'test.xml';
    $contents = '<test>This is a test</test>';
    $replace = '<replace/>';
    
    $this->session->create($this->dbname);
    
    $this->session->add($path, $contents);
    
    $this->session->replace($path, $replace);
    
    $result = $this->session->execute("XQUERY db:open('$this->dbname', '$path')");
    
    $this->assertXmlStringEqualsXmlString($replace, $result);
    
    $this->drop($this->dbname);
  }
  
  /**
   * @depends testCreate
   */
  public function testStore()
  {
    
    $path = 'raw.txt';
    
    $contents = 'raw';
    
    $this->session->create($this->dbname);
    
    $this->session->store($path, $contents);
    
    $this->assertContains($path, $this->session->execute('LIST '.$this->dbname));
    
    $this->session->execute("SET SERIALIZER method=raw");
    $this->session->execute("OPEN $this->dbname");
    $result = $this->session->execute("RETRIEVE $path");
    
    $this->assertSame($result, $contents);
    
    $this->drop($this->dbname);
  }
  
  /**
   * @depends testExecute 
   */
  public function testScript()
  {
    $script = "CREATE DB $this->dbname;DROP DB $this->dbname";
    
    $this->session->script($script);
    
    $list = $this->session->execute("LIST");
    
    
    $script = "<commands><create-db name='$this->dbname'/><open name='$this->dbname'/></commands>";
    $this->session->script($script);
    $list = $this->session->execute("LIST");
    $this->assertContains($this->dbname, $list);
    $script = "<commands><close/><drop-db name='$this->dbname'/></commands>";
    $this->session->script($script);
    $list = $this->session->execute("LIST");
    $this->assertNotContains($this->dbname, $list);
    
  }
  
  private function drop($db)
  {
    $this->session->execute('DROP DATABASE '.$db);
  }
}