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
  
  /**
   * Converts php value to string form for XQuery.
   * 
   * @param mixed $value
   * @return string
   */
  static public function value($value)
  {
    switch (true)
    {
      case false === $value:
        return 'false()';
        break;
      case true === $value:
        return 'true()';
        break;
      case null === $value:
        return '()';
        break;
      case is_array($value):
        if((bool)count(array_filter(array_keys($value), 'is_string')))
        {
          //Array is associative.
          return 'map '. self::map($value);
        }
        $result = array();
        foreach ($value as $v){
          $result[] = self::value($v);
        }
        return '('.implode(',', $result).')';
        break;
      case is_numeric($value):
        return "$value";
        break;
      case is_string($value):
      default:
        return sprintf("'%s'", $value);
        break;
    }
  }
  
  /**
   * Converts XQuery string value to php type.
   * @param string $value
   * @return mixed
   */
  static public function convert($value)
  {
    switch (true)
    {
      case 'true' === $value:
        return true;
        break;
      case 'false' === $value:
        return false;
        break;
      case is_numeric($value):
        return preg_match('/^\d+$/', $value) ? intval($value) : floatval($value);
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
      
      $parts[] = '?'.http_build_query($options);
    
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
      if(is_int($key))
      {
        $items[] = sprintf("%d := %s", $key, self::value($value));
      }
      else
      {
        $items[] = sprintf("'%s' := %s", $key, self::value($value));
      }
    }
    
    return '{'.implode(', ', $items).'}';
  }
  
  static public function camelize($string)
  {
    while (true)
    {
      $pos = strpos($string, '_');
      if(false === $pos)
      {
        break;
      }
      $string = substr($string, 0, $pos) . ucfirst(substr($string, $pos+1));
    }
    
    $string[0] = strtoupper($string[0]);
    
    return $string;
  }
  
  static public function dirname($path)
  {
    $parent = dirname($path);
    return '.' === $parent ? '' : $parent;
  }
  
  static public function relative($path, $base='')
  {
    if('' === $base)
    {
      return ltrim($path, '/');
    }
    
    if(strpos($path, $base) === 0)
    {
      return substr($path, strlen($base) + 1);
    }
    
    return false;
  }

  static public function path($argN)
  {
    $parts = func_get_args();
    $path = array();
    foreach ($parts as $p)
    {
      $p = trim($p, '/');
      if('' !== $p)
        $path[] = $p;
    }
    return implode('/', $path);
  }
  
  static public function rename($path, $name)
  {
    $parent = dirname($path);
    return '.' === $parent ? $name : $parent . '/' . $name; 
  }
  
  static public function stripXMLDeclaration($xml)
  {
    return substr($xml, strlen('<?xml version="1.0"?>'));
  }
  
  static public function date($date)
  {
    return \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $date);
  }
}
