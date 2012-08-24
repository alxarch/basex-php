<?php

namespace BaseX;

use BaseX\Document\Info;
use BaseX\Database;
use BaseX\Exception;
use \DOMDocument as XML;
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
   * @var BaseX\Document\Info
   */
  protected $info;

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


  public function __construct(Database $db, $path)
  {
    $this->db = $db;
    $this->path = $path;
  }
  
  protected function doGetContents()
  {
    if(null === $this->contents)
    {
      $this->contents = $this->getDatabase()->fetch($this->getPath(), $this->isRaw());
    }
    
    return $this->contents;
  }
  
  public function getContents()
  {
    if($this->isRaw())
    {
      return $this->doGetContents();
    }
    else
    {
      // This way any changes made to the DOM will be visible.
      return $this->getXML()->saveXML();
    }
  }
  
  protected function doSave($path)
  {
    if($this->isRaw())
    {
      $this->getDatabase()->store($path, $this->getContents());
    }
    else
    {
      $this->getDatabase()->replace($path, $this->getContents()) ;
    }
  }

  public function save()
  {
    $this->doSave($this->getPath());
    $this->info = null;
    return $this;
  }
  
  public function copy($dest)
  {
    $this->doSave($dest);
    return new Document($this->getDatabase(), $dest);
  }
  
  public function move($to)
  {
    $this->getDatabase()->rename($this->getPath(), $to);
    $this->info = null;
    $this->setPath($to);
    return $this;
  }
  
  public function delete()
  {
    $this->getDatabase()->delete($this->getPath());
    $this->setPath(null);
  }
  
  public function getDatabase()
  {
    return $this->db;
  }
  
  protected function setPath($path)
  {
    $this->path = $path;
    
    return $this;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  /**
   * Returns the contents of the document as XML.
   * 
   * @return \DOMDocument
   */
  public function getXML()
  {
    if($this->isRaw())
    {
      return null;
    }

    // Avoid cyclic calls by not using getContents directly.
    if(null === $this->xml)
    {
      $this->xml = XML::loadXML($this->doGetContents());
    }
    
    return $this->xml;
  }
  
  public function isRaw()
  {
    return $this->getInfo()->raw();
  }
  
  /**
   *
   * @return SimpleXMLElement
   */
  public function getInfo()
  {
    if(null == $this->info)
    {
      $this->reloadInfo();
    }
    
    return $this->info;
  }
  
  public function setContents($contents)
  {
    $this->contents = $contents;
    $this->xml = null;
    return $this;
  }
  
  public function reloadInfo()
  {
    $index = $this->getDatabase()->getResources($this->getPath());
    
    if(empty($index))
      throw new Exception(sprintf("No document found at path: %s.", $this->getPath()));
    
    $this->info = new Info($index[0]);
    
    return $this;
  }
}