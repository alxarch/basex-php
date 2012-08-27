<?php

namespace BaseX\Resource;

use BaseX\Database;

use \SimpleXMLElement as SimpleXml;
use \DateTime;

class Info
{
  /**
   *
   * @var SimpleXmlElement
   */
  protected $info;

  public function __construct($info)
  {
    if(is_string($info))
      $info = simplexml_load_string ($info);
    
    $this->info = $info;
  }
 
  public function raw()
  {
    return 'true' === (string) $this->info['raw'];
  }
  
  public function size()
  {
    return $this->raw() ? (integer) $this->info['size'] : null;
  }
  
  public function modified()
  {
    return (string) $this->info['modified-date'];
  }
  
  public function path()
  {
    return (string) $this->info;
  }
  
  public function type()
  {
    return (string) $this->info['content-type'];
  }
}