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
use Serializable;
use BaseX\Error\SessionError;

/**
 * Session information and options.
 * 
 * @package BaseX
 */
class SessionInfo implements Serializable
{
  /**
   *
   * @var \SimpleXMLElement
   */
  protected $xml;
  
  /**
   *
   * @var \BaseX\Session
   */
  protected $session;


  public function __construct(Session $session) {
    $this->session = $session;
  }

  /**
   * 
   * @return \BaseX\Session\SessionInfo
   */
  public function refresh()
  {
    $query = $this->session->query('db:system()')->execute();
    
    if(false === $this->unserialize($query))
    {
      throw new SessionError('Session information could not be reloaded.');
    }
    
    return $this;
  }

  public function getXML()
  {
    if($this->xml instanceof \SimpleXMLElement)
    {
      return $this->xml;
    }
    
    throw new SessionError('Session information not loaded.');
  }
  
  public function serialize() {
    return null;
  }
  
  public function unserialize($data) 
  {
    $xml = @simplexml_load_string($data);
    if($xml instanceof \SimpleXMLElement && 
            isset($xml->mainoptions) && 
            isset($xml->generalinformation) &&
            isset($xml->options))
    {
      $this->xml = $xml;
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Get the version of the server.
   * 
   * @return string 
   */
  public function version() 
  {
    return (string) $this->getXML()->generalinformation->version;
  }
  
  /**
   * Get some main option of the server.
   * 
   * @param string $name
   * @return mixed 
   */
  public function __get($name)
  {
    if(isset($this->getXML()->mainoptions->{$name}))
    {
      return (string) $this->getXML()->mainoptions->{$name};
    }
    return null;
  }
  
  public function __isset($name) {
    return isset($this->getXML()->mainoptions->{$name});
  }

  /**
   * Get some option of the server.
   * 
   * @param string $name
   * @return mixed value of the option or null if it's not set 
   */
  public function option($name)
  {
    if(isset($this->getXML()->options->{$name}))
    {
      return (string) $this->getXML()->options->{$name};
    }
    return null;
  }
  
  /**
   * Whether a name matches the current createfilter patterns.
   * 
   * @link http://docs.basex.org/wiki/Options#CREATEFILTER
   * 
   * @param string $name
   * @return boolean
   */
  public function matchesCreatefilter($name)
  {
    $patterns = explode(',', $this->getXML()->options->createfilter);
    
    foreach ($patterns as $p)
    {
      if(fnmatch($p, $name))
      {
        return true;
      }
    }
    
    return false;
  }
}