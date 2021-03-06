<?php

namespace BaseX\Symfony\Security;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Symfony\Security\User;
use BaseX\Symfony\Security\UserProvider;
use Symfony\Component\Security\Core\User\User as User2;

/**
 * Test class for UserProvider.
 * Generated by PHPUnit on 2012-10-14 at 21:31:47.
 */
class UserProviderTest extends TestCaseDb
{

  /**
   * @var UserProvider
   */
  protected $provider;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  public function setUp() {
    parent::setUp();
    $this->provider = new UserProvider($this->db, 'users');
    $this->user = new User();
    
    $this->user->setUsername('luser');
    $this->user->setPassword('1234');
            
    $this->luserdata = <<<XML
 <user>
  <username>luser</username>
  <last-login/>
  <password>pass</password>
  <disabled/>
  <locked/>
  <roles>
    <role>ROLE_ADMIN</role>
  </roles>
</user>
XML;
    $this->userdata = <<<XML
 <user>
  <username>admin</username>
  <last-login/>
  <password>1234</password>
  <disabled/>
  <locked/>
  <roles>
    <role>ROLE_ADMIN</role>
  </roles>
</user>
XML;
  }

  /**
   * @covers BaseX\Symfony\Security\UserProvider::loadUserByUsername
   * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
   */
  public function testLoadUserByUsername() {
    
    $this->provider->addUser($this->user);
    
    $u = $this->provider->loadUserByUsername('luser');
    
    $this->assertTrue($u instanceof User);
    
    $u = $this->provider->loadUserByUsername('adasff');
  }

  /**
   * @covers BaseX\Symfony\Security\UserProvider::refreshUser
   */
  public function testRefreshUser() {
    $this->provider->addUser($this->user);
    $this->db->replace('users/luser.xml', $this->luserdata);
    $u = $this->provider->refreshUser($this->user);
    $this->assertEquals('pass', $u->getPassword());
  }

  /**
   * @covers BaseX\Symfony\Security\UserProvider::supportsClass
   */
  public function testSupportsClass() {
    $u = new User2('aaa', 'aaa');
    $this->assertFalse($this->provider->supportsClass(get_class($u)));
    $this->assertTrue($this->provider->supportsClass(get_class($this->user)));
  }

  /**
   * @covers BaseX\Symfony\Security\UserProvider::get
   */
  public function testLoadUsers() {
    
    $this->assertEquals(0, count($this->provider->get()));
    $this->provider->addUser($this->user);
    $this->assertNotEmpty($this->provider->get());
  }

  /**
   * @covers BaseX\Symfony\Security\UserProvider::get
   */
  public function testGet() {
    $this->provider->addUser($this->user);
    $this->assertNull($this->provider->get('dsaf'));
    $this->assertInstanceOf(get_class($this->user),$this->provider->get('luser'));
  }

  /**
   * @covers BaseX\Symfony\Security\UserProvider::deleteUser
   */
  public function testDeleteUser() {
    $this->assertEmpty($this->ls());
    $this->provider->addUser($this->user);
    $this->assertContains('luser.xml', $this->ls());
    $this->provider->deleteUser('luser');
    $this->assertEmpty($this->ls());
   
  }

  /**
   * @covers BaseX\Symfony\Security\UserProvider::addUser
   */
  public function testAddUser() {
    $this->assertEmpty($this->ls());
    $this->provider->addUser($this->user);
    $this->assertContains('luser.xml', $this->ls());
  }

}
