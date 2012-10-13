<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Resource;

use BaseX\Resource\Interfaces\StreamableResource;
use BaseX\Resource;
use BaseX\Session\Socket;
use BaseX\Helpers as B;

/**
 * Base class for streamable resources (raw/document).
 *
 * @author alxarch
 */
abstract class Streamable extends Resource implements StreamableResource
{
  protected $mime;

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
   * Get contents of this resource.
   * 
   * @param resource $into If provided contents will be piped into this stream.
   * @return string|int Contents of the resource or number of bytes piped.
   */
  public function read($into=null)
  {
    $stream = $this->getStream('r');
    
    if(is_resource($into))
    {
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
      $contents = stream_get_contents($stream);
      fclose($stream);
      return $contents;
    }
  }
  
   /**
   * Set contents for this resource.
   * 
   * @param resource|string $data
   * @return mixed 
   */
  public function write($data)
  {
    $stream = $this->getStream('w');
    
    if(is_resource($data))
    {
      $total = stream_copy_to_stream($data, $stream);
    }
    else 
    {
      $total = fwrite($stream, $data);
    }
    
    fclose($stream);
    return $total;
  }
  
  /**
   * Whether this resource is raw.
   */
  abstract public function isRaw();
  
  /**
   * Mime type for this resource.
   * 
   * @return string
   */
  public function getContentType() {
    return $this->mime;
  }
  
  /**
   * Set mime type for this resource.
   * 
   * @param string $type
   * @return \BaseX\Resource\Streamable
   */
  public function setContentType($type){
    $this->mime = $type;
    return $this;
  }
  
  /**
   * Refreshes info for this resource.
   * 
   * @return \BaseX\Resource\Streamable Returns itself on success. 
   * NULL is returned if resource is no longer available or has changed 
   * from Raw to Document or vice versa.
   */
  public function refresh()
  {
    $resources = $this->getDatabase()->getResources($this->getPath());
    if(count($resources) === 1)
    {
      $resource = $resources[0];
      
      if($resource instanceof StreamableResource && 
         $resource->isRaw() === $this->isRaw())
      {
        $this->setContentType($resource->getContentType());
        $this->modified = $resource->getModified();
        if(method_exists($resource, 'getSize') && method_exists($this, 'setSize'))
        {
          $this->setSize($resource->getSize());
        }
        
        return $this;
      }
    }
    
    return null;
    
  }
  
  

}

