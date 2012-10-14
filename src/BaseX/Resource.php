<?php
/**
 * @package BaseX
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX;

use BaseX\Helpers as B;
use BaseX\Resource\Interfaces\ResourceInterface;

/**
 * Base class for BaseX resources.
 * 
 * @package BaseX
 */
abstract class Resource implements ResourceInterface
{
  /**
   * The database this resource belongs to.
   * 
   * @var \BaseX\Database
   */
  protected $db;
  
  /**
   * The path for this resource.
   * 
   * @var string 
   */
  protected $path;
  
  /**
   * Last modified date.
   * 
   * @var \DateTime 
   */
  protected $modified;
  
  /**
   * Whether or not this resource exists on the database.
   * 
   * @var boolean
   */
  protected $exists;


  /**
   * Creates a new resource.
   * 
   * If modified parameter is passed, it is assumed that the resource already
   * exists on the database.
   * 
   * @param \BaseX\Database $db
   * @param string $path
   * @param string $modified
   */
  public function __construct(Database $db, $path, \DateTime $modified = null)
  {
    $this->db = $db;
    $this->path = (string) $path;
    
    if(null === $modified)
    {
      $this->exists = false;
    }
    else
    {
      $this->exists = true;
      $this->modified = $modified;
    }
  }
  
  public function exists()
  {
    return $this->exists;
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
   * Copy this document to another location.
   * 
   * This will overwrite any documents at that location.
   * 
   * 
   * @param string $dest

   */
  public function copy($dest)
  {
    $this->getDatabase()->copy($this->getPath(), $dest);
  }
  
  /**
   * Move this to resource to another path.
   * 
   * @param string $dest
   * 
   * @return \BaseX\Resource $this
   */
  public function move($dest)
  {
    $this->getDatabase()->rename($this->getPath(), $dest);
    $this->path = $dest;
    $this->refresh();
    
    return $this;
  }
  
  /**
   * Move this to document to another path.
   * 
   * @param string $dest
   * 
   * @return \BaseX\Resource $this
   */
  public function rename($name)
  {
    $from = $this->getPath();
    $to = B::rename($this->getPath(), $name);
    
    $this->getDatabase()->rename($from, $to);
    $this->path = $to;
    $this->refresh();
    
    return $this;
  }
  
  /**
   * Delete this resource.
   */
  public function delete()
  {
    $this->getDatabase()->delete($this->getPath());
    $this->path = null;
    $this->modified = null;
  }

  /**
   * Reload resource info from the database.
   * 
   */
  abstract public function refresh();

  /**
   * Returns a hash value to be used as an etag.
   * 
   * @return string 
   */
  public function getEtag()
  {
    $etag = sprintf('%s/%s/%d', 
            $this->getDatabase(), 
            $this->getPath(), 
            $this->getModified()->format('Y-m-d\TH:i:s.uP'));
    
    return md5($etag);
  }
  
  /**
   * Resource path.
   * 
   * @return string
   */
  public function __toString(){
    return (string) $this->path;
  }
  
  /**
   * Resource path.
   * @return string
   */
  public function getPath(){
    if(null === $this->path)
      throw new Error('Resource has probably been deleted.');
    
    return $this->path;
  }
  
  /**
   * Resource name only.
   * 
   * @return string
   */
  public function getName(){
    return basename($this->getPath());
  }
  
  /**
   * 
   * @return \DateTime
   */
  public function getModified(){
    return $this->modified;
  }
  
  
}