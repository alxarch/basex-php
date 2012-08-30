<?php

namespace BaseX\Session;

use BaseX\Session;

class Info
{
  /**
   *
   * @var \SimpleXmlElement
   */
  protected $info;
  
  /**
   *
   * @var \BaseX\Session
   */
  protected $session;
  
  public function __construct(Session $session, \SimpleXMLElement $info = null)
  {
    $this->session = $session;
    if(null === $info)
      $this->reload();
  }
  
  public function version() 
  {
    return (string)$this->info->generalinformation->version;
  }
  
  public function __get($name)
  {
    if(isset($this->info->mainoptions->{$name}))
      return (string) $this->info->mainoptions->{$name};
    return null;
  }
  public function option($name)
  {
    if(isset($this->info->options->{$name}))
      return (string) $this->info->options->{$name};
    return null;
  }
  
  public function reload()
  {
    $data = $this->session->query("declare option output:omit-xml-declaration 'false'; db:system()")->execute();
    $this->info = new \SimpleXMLElement($data);
  }
}