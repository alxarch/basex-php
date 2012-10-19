<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Symfony\Security;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use BaseX\Helpers as B;
use Serializable;
use BaseX\Error\UnserializationError;

/**
 * User class for symfony security.
 *
 * @author alxarch
 */
class User implements AdvancedUserInterface, Serializable
{

  protected $username;
  protected $password;
  protected $last_login;
  protected $roles;
  protected $salt;
  protected $disabled;
  protected $locked = false;
  protected $expires = null;

  public function isCredentialsNonExpired()
  {
    return true;
  }

  public function isEnabled()
  {
    return !$this->disabled;
  }

  public function isAccountNonExpired()
  {
    return null === $this->expires || $this->expires > time();
  }

  public function isAccountNonLocked()
  {
    return !$this->locked;
  }

  public function eraseCredentials()
  {
    
  }

  /**
   * 
   * @param string $pass
   * @return \BaseX\Symfony\Security\User
   */
  public function setPassword($pass)
  {
    $this->password = $pass;
    return $this;
  }

  /**
   * 
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * 
   * @return string[]
   */
  public function getRoles()
  {
    return $this->roles;
  }

  /**
   * 
   * @param array $roles
   * @return \BaseX\Symfony\Security\User
   */
  public function setRoles($roles)
  {
    $this->roles = array();

    foreach ($roles as $r)
    {
      $this->roles[] = (string) $r;
    }

    return $this;
  }

  /**
   * 
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  public function lock()
  {
    $this->locked = true;
    return $this;
  }

  public function unlock()
  {
    $this->locked = false;
    return $this;
  }

  public function disable()
  {
    $this->disabled = true;
    return $this;
  }

  public function enable()
  {
    $this->disabled = false;
    return $this;
  }

  public function setExpires($expires)
  {
    if (null === $expires)
    {
      $this->expires = null;
    }
    else
    {
      $this->expires = strtotime($expires);
    }

    return $this;
  }

  /**
   * 
   * @param string $username
   * @return \BaseX\Symfony\Security\User
   * @throws \InvalidArgumentException
   */
  public function setUsername($username)
  {
    if (!preg_match('/^[a-zA-Z0-9\.\-_]+$/', $username))
    {
      throw new \InvalidArgumentException('Invalid username.');
    }

    $this->username = $username;
    return $this;
  }

  /**
   * 
   * @return string|null
   */
  public function getSalt()
  {
    return $this->salt;
  }

  public function setSalt($salt)
  {
    $this->salt = $salt;
  }

  /**
   * 
   * @return int timestamp
   */
  public function getLastLogin()
  {
    return $this->last_login;
  }

  public function setLastLogin($datetime)
  {
    if (null === $datetime)
      $this->last_login = null;
    else
      $this->last_login = strtotime($datetime);

    return $this;
  }

  public function serialize()
  {
    $xml = simplexml_load_string('<user xmlns=""/>');
    $xml->addChild('username', $this->getUsername());
    $xml->addChild('last-login', $this->getLastLogin());
    $xml->addChild('password', $this->getPassword());
    $xml->addChild('disabled', $this->disabled);

    $xml->addChild('locked', $this->locked);

    if ($this->expires)
      $xml->addChild('expires', $this->expires);

    $xml->addChild('roles');

    $roles = $this->getRoles();
    if ($roles)
    {
      foreach ($roles as $role)
      {
        $xml->roles->addChild('role', $role);
      }
    }
    return B::stripXMLDeclaration($xml->asXML());
  }

  public function unserialize($data)
  {
    $xml = @simplexml_load_string($data);

    if (false !== $xml && isset($xml->username) && isset($xml->password))
    {
      $this->setUsername((string) $xml->username)
          ->setPassword((string) $xml->password)
          ->setRoles($xml->roles->role);

      if (isset($xml->expires))
        $this->setExpires((string) $xml->expires);

      if (isset($xml->{'last-login'}))
        $this->setLastLogin((string) $xml->{'last-login'});

      if ('true' === (string) $xml->disabled)
        $this->disable();

      if ('true' === (string) $xml->locked)
        $this->lock();
      else
        $this->unlock();
    }
    else
    {
      throw new UnserializationError();
    }
  }

  public function __toString()
  {
    return $this->getUsername();
  }

}

