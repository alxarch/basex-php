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
 * Session information and orptions.
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
    return (string) $this->data->generalinformation->version;
  }
  
  /**
   * Get some main option of the server.
   * 
   * @param string $name
   * @return mixed 
   */
  public function __get($name)
  {
    if(isset($this->data->mainoptions->{$name}))
    {
      return (string) $this->data->mainoptions->{$name};
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
    if(isset($this->data->options->{$name}))
    {
      return (string) $this->data->options->{$name};
    }
    return null;
  }
}