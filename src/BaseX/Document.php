<?php

namespace BaseX;

use BaseX\Document\Info;
use BaseX\Database;
use BaseX\Exception;
use \DOMDocument as XML;

/**
 * Document abstraction class for BaseX resources.
 * 
 * @todo Instanciate/manipulate a document 'offline' and save afterwards.
 * 
 */
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

  /**
   * Constructor
   * 
   * @param Database $db
   * @param string $path 
   */
  public function __construct(Database $db, $path)
  {
    $this->db = $db;
    $this->path = $path;
  }
  
  /**
   * Intermediate helper function
   * 
   * @return string
   */
  protected function doGetContents()
  {
    if(null === $this->contents)
    {
      $this->contents = $this->getDatabase()->fetch($this->getPath(), $this->isRaw());
    }
    
    return $this->contents;
  }
  
  /**
   * Retrieves the contents of a file.
   * 
   * In case of an xml file it uses the dom and flushes it as a string.
   * 
   * @return type 
   */
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
  
  /**
   * Helper that actually saves the data.
   * 
   * @todo posibility to save to another database.
   * 
   * @param string $path 
   */
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

  /**
   * Persist any local changes.
   * 
   * @return \BaseX\Document 
   */
  public function save()
  {
    $this->doSave($this->getPath());
    $this->info = null;
    return $this;
  }
  
  /**
   * Copy this document to another location.
   * 
   * @todo posibility to copy accross databases.
   * 
   * @param type $dest
   * @return \BaseX\Document 
   */
  public function copy($dest)
  {
    $this->doSave($dest);
    return new Document($this->getDatabase(), $dest);
  }
  
  /**
   * Move this to document to another path.
   * 
   * @todo posibility to move accross databases.
   * 
   * @param type $to
   * @return \BaseX\Document 
   */
  public function move($to)
  {
    $this->getDatabase()->rename($this->getPath(), $to);
    $this->info = null;
    $this->setPath($to);
    return $this;
  }
  
  /**
   * Delete this document.
   *  
   */
  public function delete()
  {
    $this->getDatabase()->delete($this->getPath());
    $this->setPath(null);
  }
  
  /**
   * The database this document belongs to.
   * 
   * @return type 
   */
  public function getDatabase()
  {
    return $this->db;
  }
  
  /**
   * Set the path of the current document.
   * 
   * To move the document use move()
   * 
   * @param string $path
   * @return \BaseX\Document 
   */
  protected function setPath($path)
  {
    $this->path = $path;
    
    return $this;
  }
  
  /**
   * Path of this document within it's database.
   * @return type 
   */
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
  
  /**
   * Whether the document is raw file or xml document. 
   * 
   * @return boolean
   */
  public function isRaw()
  {
    return $this->getInfo()->raw();
  }
  
  /**
   * Resource info for this document.
   * 
   * @return BaseX\Document\Info
   */
  public function getInfo()
  {
    if(null == $this->info)
    {
      $this->reloadInfo();
    }
    
    return $this->info;
  }
  
  /**
   * Sets the contents of a document.
   * 
   * Storing any changes to the database must occur in a separate step.
   * 
   * Any local changes made to the xml tree will be lost.
   * 
   * @param type $contents
   * @return \BaseX\Document 
   */
  public function setContents($contents)
  {
    $this->contents = $contents;
    $this->xml = null;
    return $this;
  }
  
  /**
   * Reload resource info from the database.
   * 
   * @return \BaseX\Document $this
   * @throws Exception 
   */
  public function reloadInfo()
  {
    $index = $this->getDatabase()->getResources($this->getPath());
    
    if(empty($index))
      throw new Exception(sprintf("No document found at path: %s.", $this->getPath()));
    
    $this->info = new Info($index[0]);
    
    return $this;
  }
  
  /**
   * Reloads document contents & info from the database. 
   * 
   * Any local changes made to contents or the xml tree will be lost.
   * 
   * @return \BaseX\Document $this
   */
  public function reload()
  {
    $old = $this->getInfo()->modified();
    $this->reloadInfo();
    if($old !== $this->getInfo()->modified())
    {
      $this->doGetContents();
    }
    
    return $this;
  }
}