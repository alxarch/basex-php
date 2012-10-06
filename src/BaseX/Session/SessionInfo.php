<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Session;

use BaseX\Query\SimpleXMLResult;


/**
 * Session information and options.
 * 
 * @package BaseX
 */
class SessionInfo extends SimpleXMLResult
{
  /**
   * Get the version of the server.
   * 
   * @return string 
   */
  public function version() 
  {
    return (string) $this->xml->generalinformation->version;
  }
  
  /**
   * Get some main option of the server.
   * 
   * @param string $name
   * @return mixed 
   */
  public function __get($name)
  {
    if(isset($this->xml->mainoptions->{$name}))
    {
      return (string) $this->xml->mainoptions->{$name};
    }
    return null;
  }
  
  /**
   * Get some option of the server.
   * 
   * @param string $name
   * @return mixed value of the option or null if it's not set 
   */
  public function option($name)
  {
    if(isset($this->xml->options->{$name}))
    {
      return (string) $this->xml->options->{$name};
    }
    return null;
  }
}