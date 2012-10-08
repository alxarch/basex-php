<?php
/**
 * @package BaseX
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX;

use BaseX\Resource\ResourceInterface;
use BaseX\Resource\ResourceInfo;
use BaseX\Session;
use BaseX\Helpers as B;
use BaseX\Error;

/**
 * Resource abstraction for BaseX resources.
 * 
 * @package BaseX
 */
abstract class Resource implements ResourceInterface
{
 /**
  * Path of this resource in the database
  * 
  * @var string
  */
  protected $path;
  
  /**
   * 
   * Database name
   * 
   * @var string
   */
  protected $db;

  /**
   * Resource information
   * 
   * size, type, raw, modified
   * 
   * @var BaseX\Resource\ResourceInfo
   */
  protected $info;
  
  /**
   * Session to use for commands/queries
   * 
   * @var BaseX\Session
   */
  protected $session;
  
  /**
   * Constructor
   * 
   * @param BaseX\Session $session
   * @param string $db
   * @param string $path 
   */
  public function __construct(Session $session, $db, $path, $info=null)
  {
    if($db instanceof Database)
    {
      $this->db = $db->getName();
    }
    
    if(is_object($path) && method_exists($path, 'getPath'))
    {
      $path = $path->getPath();
    }
    
    $this->session = $session;
    $this->db = (string)$db;
    $this->path = (string)$path;
    $this->setInfo($info);
    
    $this->init();
  }
  
  /**
   * To be overriden by subclasses.
   * 
   * Runs at the end of the constructor. 
   */
  protected function init()
  {
    
  }
  
  /**
   * Set Resource info for current resource.
   * 
   * @param mixed $info
   * @return \BaseX\Resource $this
   * @throws Error 
   */
  public function setInfo($info)
  {
    if(null === $info || $info instanceof ResourceInfo)
    {
      $this->info = $info;
    }
    else
    {
      $this->info = new ResourceInfo($this->session);
      $this->info->setData($info);
    }
    
    return $this;
  }
  
  /**
   * Copy this document to another location.
   * 
   * This will overwrite any documents at that location.
   * 
   * 
   * @param string $dest
   * 
   * @return BaseX\Resource\GenericResource 
   */
  public function copy($dest)
  {
    
    $this->getCopyQuery($dest)->execute();
    
    $class = get_called_class();
    
    return new $class($this->getSession(), $this->getDatabase(), $dest);
  }
  
  /**
   * Move this to document to another path.
   * 
   * @param string $dest
   * 
   * @return BaseX\Resource\GenericResource 
   */
  public function move($dest)
  {

    $this->getMoveQuery($dest)->execute();
  
    $class = get_called_class();
    
    return new $class($this->getSession(), $this->getDatabase(), $dest);
    
  }
  
  /**
   * Delete this document.
   *  
   */
  public function delete()
  {
    $this->getDeleteQuery()->execute();
    
    $this->setPath(null);
  }
  
  /**
   * The name of the database this document belongs to.
   * 
   * @return \BaseX\Session
   */
  public function getSession()
  {
    return $this->session;
  }
  
  /**
   * The name of the database this document belongs to.
   * 
   * @return string
   */
  public function getDatabase()
  {
    return $this->db;
  }
  
  /**
   * Set the path of the current resource.
   * 
   * To actually move the document use move()
   * 
   * @param string $path
   * @return \BaseX\Resource $this
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
    return $this->getInfo()->isRaw();
  }
  
  /**
   * Resource size. 
   * 
   * @return boolean
   */
  public function getSize()
  {
    return $this->getInfo()->getSize();
  }
  
  /**
   * Resource type. 
   * 
   * @return boolean
   */
  public function getType()
  {
    return $this->getInfo()->getContentType();
  }
  
  /**
   * Resource info.
   * 
   * @return \BaseX\Resource\ResourceInfo
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
   * Reload resource info from the database.
   * 
   * @return \BaseX\Resource\Generic $this
   * @throws \BaseX\Error
   */
  public function reloadInfo() 
  {
    $info = ResourceInfo::get($this->getSession(), $this->getDatabase(), $this->getPath());
    
    $this->info = empty($info) ? null : $info[0];
    
    return $this;
  }
  
  
  /**
   * Returns a hash value to be used as an etag.
   * 
   * @return string 
   */
  public function getModified()
  {
    return $this->getInfo()->getModifiedDate();
  }
  
  /**
   * Returns a hash value to be used as an etag.
   * 
   * @return string 
   */
  public function getEtag()
  {
    $db = $this->getDatabase();
    $path = $this->getPath();
    $time = $this->getModified();
    return md5("$db/$path/$time");
  }
  
  public function getUri()
  {
    return B::uri($this->getDatabase(), $this->getPath());
  }
  
  /**
   * Return a stream handler for this resource.
   * 
   * @param string $mode valid modes: r, w
   * @return resource
   * 
   * @throws Error 
   */
  public function getStream($mode='r')
  {
    $stream = fopen($this->getUri(), $mode);
    
    if(false === $stream)
    {
      throw new Error('Failed to open resource stream.');
    }
    
    return $stream;
  }
  
  /**
   * Returns resource path.
   * 
   * @return string
   */
  public function __toString()
  {
    return $this->getPath();
  }
  
  /**
   * Instanciates a Resource from a basex:// uri.
   * 
   * Uses late static binding to return a resource of the same class as the 
   * subclass that called the method.
   * 
   * @param Session $session
   * @param string $uri
   * 
   * @return BaseX\Resource\Generic
   * 
   * @throws BaseX\Error on invalid url
   */
  static public function fromURI(Session $session, $uri)
  {
    $parts = parse_url($uri);
    if('basex' !== $parts['scheme'] || !isset($parts['host']) || !isset($parts['path']))
      throw new Error('Invalid url');
    
    $class = get_called_class();
    
    return new $class($session, $parts['host'], substr($parts['path'], 1));
  }
  
  abstract protected function getContentsQuery();
  abstract protected function getMoveQuery($dest);
  abstract protected function getCopyQuery($dest);
  abstract protected function getDeleteQuery();
}