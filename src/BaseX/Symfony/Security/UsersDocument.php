<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Symfony\Security;


use BaseX\Symfony\Security\User;

use BaseX\Resource\Document;

/**
 * Description of UsersDocument
 *
 * @author alxarch
 */
class UsersDocument extends Document
{
  public function addUser(User $user)
  {
    $db = $this->getDatabase();
    $path = $this->getPath();
    $username = $user->getUsername();
    $xml = $user->getXML();
    
    $xql = <<<XQL
      let \$users := db:open('$db', '$path')
      let \$u := \$users//user[username = '$username']
      return (
        if(\$u) then delete node \$u else (),
        insert node $xml into \$users/*:users,
        db:output('OK')
      )
XQL;
    return 'OK' === $this->getSession()->query($xql)->execute();
  }
  
  public function deleteUser($username)
  {
    $db = $this->getDatabase();
    $path = $this->getPath();
    
    $xql = <<<XQL
      let \$users := db:open('$db', '$path')
      let \$u := \$users//user[username = '$username']
      return if(\$u) 
        then (delete node \$u, db:output('OK')) 
        else db:output('No such user.')
XQL;
    
    return 'OK' === $this->getSession()->query($xql)->execute();
  }
  
  public function getUser($username)
  {
    $db = $this->getDatabase();
    $path = $this->getPath();
    $xql = "db:open('$db', '$path')//user[username = '$username']";
    $query = $this->getSession()->query($xql);
    $results = User::getForQuery($query);
    
    return empty($results) ? null : $results[0];
  }
  
  public function getUsers()
  {
    $db = $this->getDatabase();
    $path = $this->getPath();
    $xql = "db:open('$db', '$path')//user";
    $query = $this->getSession()->query($xql);
    return User::getForQuery($query);
  }
}
