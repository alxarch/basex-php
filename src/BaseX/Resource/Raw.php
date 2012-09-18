<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Resource;
use BaseX\Error;
use BaseX\Query\QueryBuilder;
use BaseX\Session\Socket;

/**
 * BaseX Resource for non xml files.
 *
 * @package BaseX 
 */
class Raw extends Resource
{
  protected function init()
  {
    if(!$this->isRaw())
    {
      throw new Error('Resource is not a raw file.');
    }
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
      return $this->getContentsQuery()->execute();
    }
  }
  
  protected function getContentsQuery() 
  {
    $xql = sprintf("db:retrieve('%s', '%s')", $this->getDatabase(), $this->getPath());
    return QueryBuilder::begin()
            ->setParameter('method', 'raw')
            ->setBody($xql)
            ->getQuery($this->getSession());
  }

  protected function getCopyQuery($dest)
  {
    $xql = sprintf(
        "(db:output('ok'), db:store('%s', '%s', db:retrieve('%s', '%s')))", 
        $this->getDatabase(), $dest, $this->getDatabase(), $this->getPath());
    
    return $this->getSession()->query($xql);
  }
  
  protected function getMoveQuery($dest) 
  {
    $xql = sprintf("db:rename('%s', '%s', '%s')", $this->getDatabase(), $this->getPath(), $dest);
    
    return $this->getSession()->query($xql);
  }
  
  protected function getDeleteQuery() 
  {
    $xql = sprintf("db:delete('%s', '%s')", $this->getDatabase(), $this->getPath());
    
    return $this->getSession()->query($xql);
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
}
