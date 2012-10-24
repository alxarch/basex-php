<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Symfony\Security;

use BaseX\Database;
use BaseX\Query\Results\UnserializableResults;
use BaseX\Symfony\Security\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * UserProvider for Symfony Security.
 *
 * @author alxarch
 */
class UserProvider implements UserProviderInterface
{

  /**
   *
   * @var string
   */
  private $path;

  /**
   *
   * @var Database
   */
  private $db;
  private $salt;

  public function __construct(Database $db, $path, $salt = null)
  {
    $this->db = $db;
    $this->path = $path;
    $this->salt = $salt;
  }

  /**
   * 
   * @param string $xpath
   * @return  UnserializableResults
   */
  protected function xpath($xpath)
  {
    return $this->db
      ->xpath($xpath, $this->path)
      ->parseObject('BaseX\Symfony\Security\User');
  }

  public function loadUserByUsername($username)
  {
    $user = $this->xpath("//user[username = '$username']")->getSingle();

    if (null === $user)
      throw new UsernameNotFoundException("Username '$username' not found.");

    $user->setSalt($this->salt);

    return $user;
  }

  public function refreshUser(UserInterface $user)
  {
    if (!$this->supportsClass(get_class($user)))
    {
      throw new UnsupportedUserException("Unsupported user class.");
    }

    return $this->loadUserByUsername($user->getUsername());
  }

  public function supportsClass($class)
  {
    return $class === 'BaseX\Symfony\Security\User';
  }

  public function get($username = null)
  {
    if (null === $username)
    {
      return $this->xpath('//user');
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
