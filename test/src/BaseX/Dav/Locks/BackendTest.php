<?php

namespace BaseX\Dav\Locks;

use BaseX\Dav\Locks\Backend;
use BaseX\PHPUnit\TestCaseDb;
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
class BackendTest extends TestCaseDb
{

  protected $backend = null;
  public $locks = array();

  /**
   * 
   * @return Backend
   */
  protected function getBackend()
  {
    if (null === $this->backend)
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
    $this->assertContains('.davlocks/'.$lock->token.'.xml', $this->ls());
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
    foreach ($this->getBackend()->getLocks('/x/k/c', false) as $lo)
    {
      $this->assertTrue(false);
    }
    
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

    $locks = $this->getBackend()->getLocks('/x/k', false);

    $this->assertEquals(2, count($locks));

    foreach ($locks as $lo)
    {
      $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $lo);
    }
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

    $locks = $this->getBackend()->getLocks('/x/k', true);

    $this->assertEquals(3, count($locks));

    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[0]);
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[1]);
    $this->assertInstanceOf('Sabre_DAV_Locks_LockInfo', $locks[2]);
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
  
  
  
  function testGetLocksEmpty()
  {
    
  }

}
