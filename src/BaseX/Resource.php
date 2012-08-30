<?php

namespace BaseX;

use BaseX\Resource\Info as ResourceInfo;
use BaseX\Database;
use BaseX\Exception;


/**
 * Document abstraction class for BaseX resources.
 * 
 * @todo Instanciate/manipulate a document 'offline' and save afterwards.
 * 
 */
class Resource
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
   * @var BaseX\Resource\Info
   */
  protected $info=null;

  /**
   *
   * @var string
   */
  protected $contents = null;
  

  /**
   * Constructor
   * 
   * @param Database $db
   * @param string $path 
   */
  public function __construct(Database $db, $path, ResourceInfo $info=null)
  {
    $this->db = $db;
    $this->path = $path;
    $this->info = $info;
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
   * @return string 
   */
  public function getContents()
  {
    return $this->doGetContents();
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
   * @return \BaseX\Resource 
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
   * @return \BaseX\Resource 
   */
  public function copy($dest)
  {
    $this->doSave($dest);
    $class = get_called_class();
    return new $class($this->getDatabase(), $dest);
  }
  
  /**
   * Move this to document to another path.
   * 
   * @todo posibility to move accross databases.
   * 
   * @param string $to
   * @return \BaseX\Resource 
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
   * @return \BaseX\Database 
   */
  public function getDatabase()
  {
    return $this->db;
  }
  
  /**
   * Set the path of the current resource.
   * 
   * To move the document use move()
   * 
   * @param string $path
   * @return \BaseX\Resource 
   */
  protected function setPath($path)
  {
    $this->path = $path;
    
    return $this;
  }
  
  /**
   * Path of this resource within it's database.
   * @return string 
   */
  public function getPath()
  {
    return $this->path;
  }
  
  /**
   * Filenamne of this resource within it's database.
   * @return string 
   */
  public function getName()
  {
    return basename($this->getPath());
  }
  
  /**
   * Whether the resource is raw file or xml document. 
   * 
   * @return boolean
   */
  public function isRaw()
  {
    return $this->getInfo()->raw();
  }
  
  /**
   * Resource info.
   * 
   * @return BaseX\Resource\Info
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
   * Sets the contents of a resource.
   * 
   * Storing any changes to the database must occur in a separate step.
   * 
   * Any local changes will be lost.
   * 
   * @param type $contents
   * @return \BaseX\Resource 
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
   * @return \BaseX\Resource $this
   * @throws Exception 
   */
  public function reloadInfo()
  {
    $resources = $this->getDatabase()->getResourceInfo($this->getPath());
 
    if(empty($resources))
//      $this->info = null;
      throw new Exception(sprintf("No document found at path: %s.", $this->getPath()));
    else
      $this->info = $resources[0];
  
    return $this;
  }
  
  /**
   * Reloads resource contents & info from the database. 
   * 
   * Any local changes made to contents or the xml tree will be lost.
   * 
   * @return \BaseX\Resource $this
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
  
  /**
   * Returns a hash value to be used as an etag.
   * 
   * @return string 
   */
  public function etag()
  {
    $db = $this->getDatabase()->getName();
    $path = $this->getPath();
    $time = $this->getInfo()->modified();
    return md5("$db/$path/$time");
  }
//  
//  public function exists()
//  {
//    return $this->getDatabase()->exists($this->getPath());
//  }
}