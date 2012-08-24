<?php

namespace BaseX;

class Helpers
{
//  static public function command($command)
//  {
//    if(func_num_args() > 1)
//    {
//      $args = func_get_args();
//      $command = call_user_func_array('sprintf', $args);
//    }
//    return $command;
//  }

  static public function escape($string)
  {
    $string = addcslashes($string, '"');
//    $string = str_replace(chr(255), chr(255).chr(255), $string);
//    $string = str_replace(chr(0), chr(255).chr(0), $string);
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
  
};

