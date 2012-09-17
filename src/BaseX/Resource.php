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
use BaseX\Resource\ResourceInfoProvider;
use BaseX\Session;
use BaseX\Session\Socket;
use BaseX\Helpers as B;
use BaseX\Error;
use BaseX\Database;

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
    $this->session = $session;
    $this->db = $db;
    $this->path = $path;
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
   * Checks if info data is valid.
   * 
   * @param \SimpleXMLElement $info
   * @return boolean
   */
  public function validateInfo(\SimpleXMLElement $info)
  {
      return isset($info['raw']) && 
             isset($info['content-type']) && 
             isset($info['modified-date']);
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
      $this->info = new ResourceInfo();
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
    
    $db = $this->getDatabase();
    $source = $this->getPath();

    $xql = <<<XQL
      let \$raw := db:is-raw('$db', '$source')
      let \$contents := if(\$raw) 
        then db:retrieve('$db', '$source')
        else db:open('$db', '$source')
      return 
      (
        db:output('ok'),
        if(\$raw)
          then db:store('$db', '$dest', \$contents)
          else db:replace('$db', '$dest', \$contents)
      )
XQL;

    $this->getSession()->query($xql)->execute();
    
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
    $db = $this->getDatabase();
    $source = $this->getPath();
    
    $xql = "db:rename('$db', '$source', '$dest')";
    
    $this->getSession()->query($xql)->execute();
  
    $class = get_called_class();
    
    return new $class($this->getSession(), $this->getDatabase(), $dest);
    
  }
  
  /**
   * Delete this document.
   *  
   */
  public function delete()
  {
    $db = $this->getDatabase();
    $path = $this->getPath();
    
    $xql = "db:delete('$db', '$path')";
    
    $this->session->query($xql)->execute();
    
    $this->setPath(null);
  }
  
  /**
   * The name of the database this document belongs to.
   * 
   * @return BaseX\Session
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
   * Set contents for this resource.
   * 
   * @param resource|string $data
   * @return mixed 
   */
  public function setContents($data)
  {
    $stream = $this->getStream('w');
    
    if(is_resource($data))
    {
      return stream_copy_to_stream($data, $stream);
    }
    else 
    {
      return fwrite($stream, $data);
    }
    
    fclose($stream);
  }
  
  /**
   * Get contents of this resource.
   * 
   * @param resource $into If provided contents will be piped into this stream.
   * @return string|int Contents of the resource or number of bytes piped.
   */
  public function getContents($into=null)
  {
    if(is_resource($into))
    {
      $stream = $this->getStream('r');
      
      $total = 0;
      while(!feof($stream))
      {
        $total += fwrite($into, fread ($stream, Socket::BUFFER_SIZE));
      }
      
      fclose($stream);
      
      return $total;
    }
    else 
    {
      $db = new Database($this->session, $this->getDatabase());
      
      return $db->fetch($this->getPath(), $this->isRaw());
    }
    
    
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
}