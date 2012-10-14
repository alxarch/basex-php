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
use BaseX\Error;

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
    $stream = @fopen($this->getUri(), $mode);
    
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
   * @return int 
   */
  public function write($input)
  {
    $output = $this->getStream('w');
    
    $total = 0;
    
    if(is_resource($input))
    {
      while(!feof($input))
      {
        $total += fwrite($output, fread($input, Socket::BUFFER_SIZE));
      }
    }
    else 
    {
      $total = fwrite($output, $input);
    }
    
    fclose($output);
    
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
    $xml = $this->getDatabase()->getResource($this->getPath(), new \BaseX\Query\Result\SimpleXMLMapper());
    
    if($xml || $this->isRaw() !== ('true' === $xml['raw']))
    {
      $this->path = (string) $xml;
      $this->modified = B::date((string) $xml['modified-date']);
      $this->mime = (string) $xml['content-type'];
      if(isset($xml['size']) && method_exists($this, 'setSize'))
      {
        $this->setSize((int)$xml['size']);
      }
      
      return $this;
    }
    else
    {
      return null;
    }
    
  }

}

