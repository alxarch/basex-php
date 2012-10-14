<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Symfony\Security;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use BaseX\Database;
use BaseX\Symfony\Security\User;
use BaseX\Query\Result\MapperInterface;
use BaseX\Query;

/**
 * UserProvider for Symfony Security.
 *
 * @author alxarch
 */
class UserProvider implements UserProviderInterface, MapperInterface
{
  /**
   *
   * @var string
   */
  private $path;
  
  /**
   *
   * @var \BaseX\Database
   */
  private $db;
  
  private $salt;


  public function __construct(Database $db, $path, $salt=null) 
  {
    $this->db = $db;
    $this->path = $path;
    $this->salt = $salt;
  }
  
  public function supportsType($type)
  {
    return $type === Query::TYPE_ELEMENT || $type === Query::TYPE_DOCUMENT;
  }
  
  public function getResult($data, $type) 
  {
    $user = new User();
    $user->unserialize($data);
    $user->setSalt($this->salt);
    return $user;
  }
  
  public function loadUserByUsername($username)
  {
    $users = $this->db->xpath("//user[username = '$username']", $this->path, $this);
    
    if(count($users) === 1)
    {
      return $users[0];
    }
    
    throw new UsernameNotFoundException("Username '$username' not found.");
    
  }

  public function refreshUser(UserInterface $user)
  {
    if(!$this->supportsClass(get_class($user)))
    {
      throw new UnsupportedUserException("Unsupported user class.");
    }
    
    return $this->loadUserByUsername($user->getUsername());
  }
  
  public function supportsClass($class) {
    return $class === 'BaseX\Symfony\Security\User';
  }
  
  public function get($username=null)
  {
    if(null === $username)
    {
      return $this->db->xpath('//user', $this->path, $this);
    }
    
    try
    {
      return $this->loadUserByUsername($username);
    }
    catch (UsernameNotFoundException $e)
    {
      return null;
    }
  }
  
  public function deleteUser($username)
  {
    $this->db->delete("$this->path/$username.xml");
  }
  
  public function addUser(User $user)
  {
    $this->db->replace("$this->path/$user.xml", $user->serialize());
  }

}
