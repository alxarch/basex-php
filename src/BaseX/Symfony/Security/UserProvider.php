<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Symfony\Security;


use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use BaseX\Symfony\Security\UsersDocument;

/**
 * Description of UserProvider
 *
 * @author alxarch
 */
class UserProvider implements UserProviderInterface
{
  /**
   *
   * @var \Symfony\Component\Security\BaseX\UsersDocument
   */
  private $users;
  
  public function __construct(UsersDocument $users) 
  {
    $this->users = $users;
  }
  
  public function loadUserByUsername($username) {
    $user = $this->users->getUser($username);

    if(null === $user)
    {
      throw new UsernameNotFoundException("Username '$username' not found.");
    }
    
    return $user;
    
  }

  public function refreshUser(UserInterface $user){
    if(!$this->supportsClass(get_class($user)))
    {
      throw new UnsupportedUserException("Unsupported user class.");
    }
    
    return $this->loadUserByUsername($user->getUsername());
  }
  
  public function supportsClass($class) {
    return $class === 'BaseX\Symfony\Security\User';
  }
}
