<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Session;

use BaseX\Session;

/**
 * Session information and orptions.
 * 
 * @package BaseX
 */
class SessionInfo
{
  /**
   *
   * @var \SimpleXMLElement
   */
  protected $info;
  
  /**
   *
   * @var \BaseX\Session
   */
  protected $session;
  
  /**
   * Constructor
   * 
   * Info is loaded from the database if initial info is not provided.
   * 
   * @param Session $session
   * @param \SimpleXMLElement $info 
   */
  public function __construct(Session $session, \SimpleXMLElement $info = null)
  {
    $this->session = $session;
    if(null === $info)
      $this->reload();
  }
  
  /**
   * Get the version of the server.
   * 
   * @return string 
   */
  public function version() 
  {
    return (string)$this->info->generalinformation->version;
  }
  
  /**
   * Get some main option of the server.
   * 
   * @param string $name
   * @return mixed 
   */
  public function __get($name)
  {
    if(isset($this->info->mainoptions->{$name}))
      return (string) $this->info->mainoptions->{$name};
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
    if(isset($this->info->options->{$name}))
      return (string) $this->info->options->{$name};
    return null;
  }
  
  /**
   * Reloads session info. 
   */
  public function reload()
  {
    $data = $this->session->query("declare option output:omit-xml-declaration 'false'; db:system()")->execute();
    $this->info = new \SimpleXMLElement($data);
  }
}