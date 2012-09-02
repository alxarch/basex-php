<?php

namespace BaseX;

use BaseX\Session\Socket;

class Helpers
{

  static public function escape($string)
  {
    $string = addcslashes($string, '"');
    return $string;
  }
  
  static public function options($opts, $glue=",")
  {
    $result = array();
    foreach($opts as $name => $value)
    {
      if(true === $value)
        $value = 'true';
      if(false === $value)
        $value = 'false';
      
      $result[] = "$name=$value";
    }
    
    return implode($glue, $result);
  }
  
  static public function value($value)
  {
    switch (true)
    {
      case true === $value:
        return 'true';
        break;
      
      case false === $value:
        return 'false';
        break;
      
      default:
        return $value;
        break;
    }
  }
 
  
  /**
   * Restores NUL and \xFF characters in received strings.
   * 
   * @see http://docs.basex.org/wiki/Server_Protocol#Transfer_Protocol
   * 
   * @param string $data
   * @return string 
   */
  static public function unscrub($data)
  {
    $data = str_replace(Socket::PADDED_NUL, Socket::NUL, $data);
    $data = str_replace(Socket::PADDED_PAD, Socket::PAD, $data);
    return $data;
  }
  
  /**
   * Scrubs NUL characters off a string.
   * 
   * @see http://docs.basex.org/wiki/Server_Protocol#Transfer_Protocol
   * 
   * @param string $data
   * @return string 
   */
  static public function scrub($data)
  {
    $data = str_replace(Socket::PAD, Socket::PADDED_PAD, $data);
    $data = str_replace(Socket::NUL, Socket::PADDED_NUL, $data);
    return $data;
  }
  
}
