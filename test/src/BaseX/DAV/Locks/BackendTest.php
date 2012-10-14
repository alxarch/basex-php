<?php

namespace BaseX\DAV;

use BaseX\PHPUnit\TestCaseDb;

use BaseX\DAV\Locks\Backend;

use BaseX\DAV\Locks\LockInfo;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DAVLockBackendTest
 *
 * @author alxarch
 */
class LocksBackendTest extends TestCaseDb
{
  protected $backend = null;
  
  public $locks = array();
  /**
   * 
   * @return \BaseX\DAV\Locks\Backend
   */
  protected function getBackend()
  {
    if(null === $this->backend)
    {
      $this->backend = new Backend($this->db, '.davlocks');
    }
    
    return $this->backend;
  }
  
  protected function getLock($uri = 'test.xml')
  {
    $lock = new LockInfo();
    
    $lock->uri = $uri;
    $lock->created = time();
    $lock->timeout = 3600;
    $lock->token = isset($this->locks[$uri]) ? $this->locks[$uri] : md5(time());
    $lock->owner = 'username';
    $lock->scope = LockInfo::SHARED;
    $lock->depth = 0;
    $this->locks[$uri] = $lock->token;
    return $lock;
  }


  function testLock()
  {
    $lock = $this->getLock();
    $this->assertTrue($this->getBackend()->lock('test.xml', $lock));
    $this->assertContains('.davlocks/test.xml/lock.xml', $this->ls());
    return $lock;
  }
  

  function testUnLock()
  {
    $lock = $this->getLock();
    $this->assertTrue($this->getBackend()->unlock('test.xml', $lock));
    return $lock;
  }
  
  function testGetLocksAndParents()
  {
    $lock = $this->getLock();
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
  
  
  function testGetLocksAndChildren()
  {
    $lock = $this->getLock();
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
    
    $this->assertInstanceOf('BaseX\Dav\Locks\LockInfo', $locks[0]);
    $this->assertInstanceOf('BaseX\Dav\Locks\LockInfo', $locks[1]);
    $this->assertInstanceOf('BaseX\Dav\Locks\LockInfo', $locks[2]);
  }
  
  function testGetLock()
  {
    $lock = $this->getLock();
    
    $this->assertTrue($this->getBackend()->lock($lock->uri, $lock));
    
    $locks = $this->getBackend()->getLocks($lock->uri, false);
    $this->assertNotEmpty($locks);
    $this->assertEquals(1, count($locks));
    
    $actual = $locks[0];
    
    $this->assertEquals($actual->uri, $lock->uri);
    $this->assertEquals($actual->created, $lock->created);
    $this->assertEquals($actual->timeout, $lock->timeout);
    $this->assertEquals($actual->depth, $lock->depth);
    $this->assertEquals($actual->scope, $lock->scope);
    $this->assertEquals($actual->owner, $lock->owner);
    
  }
}
