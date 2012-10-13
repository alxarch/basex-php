<?php

namespace BaseX\DAV;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Dav\Locks\LockInfo;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DAVLockBackendTest
 *
 * @author alxarch
 */
class LockInfoTest extends TestCaseDb
{
  public function testSerialize()
  {
    $lock = new LockInfo();
    
    $lock->uri = 'test.xml';
    $lock->created = time();
    $lock->timeout = 3600;
    $lock->token = md5(time());
    $lock->owner = 'username';
    $lock->scope = LockInfo::SHARED;
    $lock->depth = 0;
    
    $data = $lock->serialize();
    
    
    $expected = <<<XML
 <lock xmlns="">
   <created>$lock->created</created>
   <timeout>$lock->timeout</timeout>
   <token>$lock->token</token>
   <uri>$lock->uri</uri>
   <owner>$lock->owner</owner>
   <depth>$lock->depth</depth>
   <scope>$lock->scope</scope>
   </lock>
XML;
    
    $this->assertXmlStringEqualsXmlString($expected, $data);
  }
  
  public function testUnerialize()
  {
    $time = time();
    $token = md5($time);
    $scope = LockInfo::SHARED;
    
    $data = <<<XML
 <lock>
   <owner>username</owner>
   <created>$time</created>
   <timeout>3600</timeout>
   <depth>0</depth>
   <token>$token</token>
   <scope>$scope</scope>
   <uri>test.xml</uri>
   </lock>
XML;
    
    $lock = new LockInfo();
    
    $lock->unserialize($data);
    
    $this->assertEquals($time, $lock->created);
    $this->assertEquals($token, $lock->token);
    $this->assertEquals('test.xml', $lock->uri);
    $this->assertEquals($scope, $lock->scope);
    $this->assertEquals('username', $lock->owner);
    $this->assertEquals(0, $lock->depth);
    $this->assertEquals(3600, $lock->timeout);
    
  }
  
}
