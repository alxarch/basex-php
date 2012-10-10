<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Symfony\Security;

use BaseX\Query\QueryResult;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Description of User
 *
 * @author alxarch
 */
class User extends QueryResult implements UserInterface
{
  static private $salt = null;
  
  protected $username;
  protected $password;
  protected $last_login;
  protected $roles;
  
  public function setData($data) {
    parent::setData($data);
    $xml = @simplexml_load_string($data);
    
    $this->setUsername((string) $xml->username)
         ->setPassword((string) $xml->password)
         ->setLastLogin(strtotime((string) $xml->{'last-login'}));
         
    $this->roles = array();
    foreach ($xml->roles->role as $r)
    {
       $this->roles[] = new Role((string)$r);
    }
    
    return $this;
    
  }
  
  public function eraseCredentials()
  {
  }
  
  public function setPassword($pass)
  {
    $this->password = $pass;
    return $this;
  }
  
  public function getPassword() {
    return $this->password;
  }
  
  public function getRoles() {
    return $this->roles;
  }
  
  public function setRoles($roles) {
    $this->roles = array();
    
    foreach ($roles as $r)
    {
      $this->roles[] = new Role((string)$r);
    }
    
    return $this;
  }
  
  public function getUsername() {
    return $this->username;
  }
  
  public function setUsername($username) {
    if(!preg_match('/^[a-zA-Z0-9\.\-_]+$/', $username))
    {
      throw new \InvalidArgumentException('Invlid username.');
    }
    
    $this->username = $username;
    return $this;
  }
  
  public function getSalt()
  {
    return self::$salt;
  }
  
  public static function setSalt($salt)
  {
    self::$salt = $salt;
  }
  
  public function getLastLogin()
  {
    return $this->last_login;
  }
  
  public function setLastLogin($timestamp)
  {
    $this->last_login = (int) $timestamp;
    return $this;
  }
  
  public function getXML()
  {
    $xml = simplexml_load_string('<user xmlns=""/>');
    $xml->addChild('username', $this->getUsername());
    $xml->addChild('last-login', $this->getLastLogin());
    $xml->addChild('password', $this->getPassword());
    $xml->addChild('roles');
    foreach ($this->getRoles() as $role)
    {
      $xml->roles->addChild('role', $role->getRole());
    }
    
    return substr($xml->asXML(), strlen('<?xml version="1.0"?>'));
  }
  
  public function __toString() {
    return $this->getUsername();
  }
}

