<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\DAV;

use BaseX\DAV\ObjectTree;
use BaseX\Helpers as B;
use Sabre_DAV_File;

/**
 * WebDAV file node representing a BaseX resource.
 * 
 * @package BaseX
 * 
 */
class ResourceNode extends Sabre_DAV_File
{
  /**
   * @var \BaseX\DAV\ObjectTree
   */
  protected $tree;
  public $path;
  public $modified;
  public $mime;
  public $size;
  public $etag;
  
  public function __construct(ObjectTree $tree, $path)
  {
    $this->tree = $tree;
    $this->path = $path;
  }
  
  public function getLastModified() {
    return $this->modified;
  }
  
  public function getName()
  {
    return basename($this->path);
  }
  
  public function getSize() 
  {
    return $this->size;
  }
  
  public function getContentType()
  {
    return $this->mime;
  }
  
  public function setName($name)
  {
    $this->tree->move($this->path, B::rename($this->path, $name));
  }
  
  public function put($data)
  {
    $uri = $this->tree->getURI($this->path);
    $dest = fopen($uri, 'w');
    
    if(is_resource($data))
    {
      stream_copy_to_stream($data, $dest);
    }
    else
    {
      fwrite($dest, $data);
    }
    
    fclose($dest);
  }

  public function get()
  {
    $uri = $this->tree->getURI($this->path);
    return fopen($uri, 'r');
  }

  public function getETag() 
  {
   return sprintf('"%s"', $this->etag);
  }
  
  public function delete()
  {
    $this->tree->delete($this->path);
  }
  
}
