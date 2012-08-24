<?php

namespace BaseX;

use BaseX\Database;
use BaseX\Exception;

class Document
{
  /**
  *
  * @var string
  */
  protected $path;
  
  /**
   * 
   * @var \BaseX\Database
   */
  protected $db;

  /**
   *
   * @var boolean
   */
  protected $raw = true;

  /**
   *
   * @var string
   */
  protected $type;

  /**
   *
   * @var DateTime
   */
  protected $updated = null;

  /**
   *
   * @var integer
   */
  protected $size;
  
  /**
   *
   * @var string
   */
  protected $contents = null;
  
  /**
   *
   * @var \DOMDocument
   */
  protected $xml = null;


  public function __construct(Database $db, $path = null)
  {
    $this->db = $db;
    $this->path = $path;
    
    $this->info();
  }
  
  public function getContents()
  {
    if(null === $this->contents)
    {
      $this->getDatabase()->retrieve($this->getPath());
    }
    
    return $this->contents;
  }
  
  public function save($as = null)
  {
    $dest = null === $as ? $this->getPath() : (string) $as;
    
    $success = $this->raw ?
        $this->getDatabase()->add($dest, $this->getContents()) :
        $this->getDatabase()->store($dest, $this->getContents());
    
    if($success)
    {
      $this->info();
    }
    
    return $success;
  }
  
  public function move($to)
  {
    return $this->getDatabase()->rename($this->getPath(), $to);
  }
  
  public function delete()
  {
    return $this->getDatabase()->delete($this->getPath());
  }
  
  public function getDatabase()
  {
    return $this->db;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function exists()
  {
    return null !== $this->updated;
  }
  
  /**
   * Returns the contents of the document as XML.
   * 
   * @return \DOMDocument
   */
  public function getXML()
  {
    if(!$this->raw && null === $this->xml)
    {
      $this->xml = \DOMDocument::loadXML($this->getContents());
    }
    
    return $this->xml;
  }
  
  protected function info()
  {
    $resources = $this->db->index($path);
    if($resources)
    {
      $info = $resources[0];
      $this->type = $info['content-type'];
      $this->raw = $info['raw'];
      $this->size = isset($info['size']) ? (int) $info['size'] : null;
      $this->updated = new \DateTime($info['updated']);
    }
  }
}