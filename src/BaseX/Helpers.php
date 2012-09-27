<?php
/**
 * @package BaseX
 *  
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX;

use BaseX\Session\Socket;
use BaseX\StreamWrapper;
use BaseX\Resource;
use BaseX\Database;

/**
 * Helper functions
 * 
 * @package BaseX 
 */
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
   * @link http://docs.basex.org/wiki/Server_Protocol#Transfer_Protocol
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
   * @link http://docs.basex.org/wiki/Server_Protocol#Transfer_Protocol
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
  
  
  /**
   * Helper function to build URIs for BaseX stream wrapper.
   * 
   * @param string $db
   * @param string $path
   * @param string $parser
   * @param array $options
   * 
   * @return string 
   */
  static public function uri($db, $path, $parser=null, $options=array())
  {
    $parts = array(StreamWrapper::NAME, '://', $db, '/', $path);
   
    if(is_array($options) && !empty($options))
    {
      
      $keys = array_keys($options);
      $values = array_values($options);

      array_map('strtolower', $keys);

      $options = array_combine($keys, $values);

      if(isset($options['parseopt']) && is_array($options['parseopt']))
      {
        $options['parseopt'] = self::options($options['parseopt']);
      }

      if(isset($options['htmlopt']) && is_array($options['htmlopt']))
      {
        $options['htmlopt'] = self::options($options['htmlopt']);
      }
      
      $parts[] = http_build_query($options);
    
    }
    
    if(null !== $parser)
    {
      $parts[] = "#$parser";
    }
    
    return implode($parts);
  }
  
  /**
   * Serializes an array into an XQuery map declaration.
   * 
   * @param array $map
   * @return string
   */
  static public function map($map)
  {
    $items = array();
    foreach ($map as $key => $value)
    {
        if(false === $value) $value = 'false';
        if(true === $value) $value = 'true';
        if(null === $value) $value = '()';
        $items[] = sprintf("%s := %s", $key, $value);
    }
    return '{'.implode(', ', $items).'}';
  }
}
