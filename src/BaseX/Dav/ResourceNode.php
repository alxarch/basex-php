<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Dav;

use BaseX\Dav\Node;

/**
 * WebDAV file node representing a BaseX resource.
 * 
 * @package BaseX
 * 
 */
class ResourceNode extends Node implements \Sabre_DAV_IFile
{

  public $mime;
  public $size;
  public $resource;
  
  public function getSize(){
    return $this->size;
  }
  
  public function getContentType(){
    return $this->mime;
  }
  
  public function put($data){
    $this->tree->put($this, $data);
  }

  public function get(){
    return fopen($this->tree->getURI($this), 'r');
  }

  public function getETag() {
    return $this->tree->getEtag($this);
  }
  
}
