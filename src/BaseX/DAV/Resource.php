<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\DAV;

use \BaseX\DAV\Tree;
use BaseX\Resource\ResourceInterface;

use Sabre_DAV_File;

/**
 * WebDAV file node representing a BaseX resource.
 * 
 * @package SabreDAV-BaseX
 * 
 */
class Resource extends Sabre_DAV_File
{
  /**
   * @var \BaseX\Resource
   */
  protected $resource;
  
  /**
   * @var \Sabre\DAV\BaseX\Tree The originating tree.
   */
  protected $tree;
  
  public function __construct(Tree $tree, ResourceInterface $resource)
  {
    $this->resource = $resource;
    $this->tree = $tree;
  }
  
  /**
   * 
   * @return \BaseX\Resource\ResourceInterface
   */
  public function getResource()
  {
    return $this->resource;
  }
  
  public function getTree()
  {
    return $this->tree;
  }

  public function getName()
  {
    return $this->getResource()->getName();
  }
  
  public function setName($name)
  {
    $dest = sprintf('%s/%s', $this->getPath(), $name);
    
    $this->getTree()->move($this->getFullPath(), $dest);
  }
  
  public function getPath()
  {
    $path = dirname($this->getResource()->getPath());
    return '.' === $path ? '' : $path;
  }
  
  public function getResourceUri()
  {
    $resource = $this->getResource();
    if($resource->isRaw())
    {
      $dir = $this->getTree()->getDirectory();
      if($dir)
      {
        $filename = $dir . DIRECTORY_SEPARATOR . $resource->getPath();
        if(is_file($filename))
        {
          return $filename;
        }
      }
    }
    
    return $resource->getUri();
  }

  public function get()
  {
    return fopen($this->getResourceUri(), 'r');
  }

  public function getSize() 
  {
    return (int) $this->getResource()->getSize();
  }
  
  public function getContentType()
  {
    return $this->getResource()->getType();
  }

  public function getETag() 
  {
    $name = $this->resource->getDatabase();
    $path = $this->resource->getPath();
    $date = $this->resource->getModified();
    $etag = md5("$name/$path/$date");
    return sprintf('"%s"', $etag);
  }
  
  public function delete()
  {
    $this->getTree()->delete($this->getFullPath());
  }
  
  public function put($data)
  {
    $dest = fopen($this->getResourceUri(), 'w');
    
    if(is_resource($data))
    {
      stream_copy_to_stream($data, $dest);
    }
    else
    {
      $tmp = fopen("php://temp", 'r+');
      fwrite($tmp, $data);
      rewind($tmp);
      stream_copy_to_stream($tmp, $dest);
      fclose($tmp);
    }
    
    fclose($dest);
  }
  
  public function getFullPath()
  {
    return $this->resource->getPath();
  }
}
