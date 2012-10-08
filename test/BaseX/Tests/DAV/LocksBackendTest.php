<?php

namespace BaseX\Tests\DAV;

use BaseX\PHPUnit\TestCaseDb;

use BaseX\DAV\Locks\Backend;

use Sabre_DAV_Locks_LockInfo as LockInfo;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DAVLockBackendTest
 *
 * @author alxarch
 */
class BaseXTest extends TestCaseDb
{
  protected $backend = null;


  protected function getBackend($db = null)
  {
    if(null === $db)
    {
      $db = $this->db;
    }
    if(null === $this->backend)
    {
      $this->session->execute("open $db");
      $this->session->replace('._davlocks.xml', '<locks/>');
      $this->backend = new Backend($this->session, $this->dbname, '._davlocks.xml');
    }
    
    return $this->backend;
  }
  
  protected function getLock($uri = 'test.xml')
  {
    $lock = new LockInfo();
    
    $lock->uri = $uri;
    $lock->created = time();
    $lock->timeout = 3600;
    $lock->token = md5(time());
    $lock->owner = 'username';
    $lock->scope = LockInfo::SHARED;
    $lock->depth = 0;
    
    return $lock;
  }


  function testLock()
  {
    $lock = $this->getLock();
    
    $this->assertTrue($this->getBackend()->lock('test.xml', $lock));
    
    $expect = "<lock token='$lock->token' created='$lock->created' owner='$lock->owner' uri='$lock->uri' depth='$lock->depth' scope='$lock->scope' timeout='$lock->timeout'/>";
    
    $xql = "db:open('$this->dbname', '._davlocks.xml')//lock";
    
    $this->assertXmlStringEqualsXmlString($expect, $this->session->query($xql)->execute());
    
    return $lock;
  }
  
  /**
   * @depends testLock 
   */
  function testUnLock(LockInfo $lock)
  {
    $this->assertTrue($this->getBackend()->lock('test.xml', $lock));
    
    $this->assertTrue($this->getBackend()->unlock($lock->uri, $lock));
    
    $xql = "db:open('$this->dbname', '._davlocks.xml')//lock";
    
    $this->assertEmpty($this->session->query($xql)->execute());
    
    return $lock;
  }
  
  /**
   * @depends testUnLock 
   */
  function testUnLockWrongToken(LockInfo $lock)
  {
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $token = $lock->token;
    
    $lock->token = 'whatever';
    
    $this->assertFalse($this->getBackend()->unlock($lock->uri, $lock));
    
    $lock->token = $token;
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
  }
  
  /**
   * @depends testLock 
   */
  function testReLock(LockInfo $lock)
  {
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $lock->scope = LockInfo::EXCLUSIVE;
    
    $lock->created += 500;
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $expect = "<lock token='$lock->token' created='$lock->created' owner='$lock->owner' uri='$lock->uri' depth='$lock->depth' scope='$lock->scope' timeout='$lock->timeout'/>";
    
    $xql = "db:open('$this->dbname', '._davlocks.xml')//lock";
    
    $this->assertXmlStringEqualsXmlString($expect, $this->session->query($xql)->execute());
    
  }
  
  /**
   * @depends testLock 
   */
  function testGetLocks(LockInfo $lock)
  {
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $lock->token = md5($lock->token);
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $locks = $this->getBackend()->getLocks($lock->uri, false);
    
    $this->assertEquals(2, count($locks));
    
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[0]);
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[1]);
  }
  
  /**
   * @depends testLock 
   */
  function testGetLocksAndParents(LockInfo $lock)
  {
    
    $lock->uri = '/x/k/c/d';
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $lock->token = md5($lock->token);
    $lock->uri = '/x/k';
    $lock->depth = 1;
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $lock->token = md5($lock->token);
    $lock->uri = '/x';
    $lock->depth = 0;
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $lock->token = md5($lock->token);
    $lock->uri = '/x/k/c';
    $lock->depth = 0;
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $locks = $this->getBackend()->getLocks('/x/k/c', false);
    
    $this->assertEquals(2, count($locks));
    
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[0]);
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[1]);
  }
  
  /**
   * @depends testLock 
   */
  function testGetLocksAndChildren(LockInfo $lock)
  {
    
    $lock->uri = '/x/k/c/d';
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $lock->token = md5($lock->token);
    $lock->uri = '/x/k';
    $lock->depth = 1;
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $lock->token = md5($lock->token);
    $lock->uri = '/x/k/c';
    $lock->depth = 0;
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $locks = $this->getBackend()->getLocks('/x/k/c', true);
    
    $this->assertEquals(3, count($locks));
    
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[0]);
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[1]);
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[2]);
  }
  
  function testGetLock()
  {
    
    $backend =  $this->getBackend();
    
    $lock = $this->getLock();
    
    $backend->lock('test.xml', $lock);
    
    $locks = $backend->getLocks('test.xml', false);
    
    $actual = $locks[0];
    
    $this->assertEquals($actual->uri, $lock->uri);
    $this->assertEquals($actual->created, $lock->created);
    $this->assertEquals($actual->timeout, $lock->timeout);
    $this->assertEquals($actual->depth, $lock->depth);
    $this->assertEquals($actual->scope, $lock->scope);
    $this->assertEquals($actual->owner, $lock->owner);
    
  }
}

?>
