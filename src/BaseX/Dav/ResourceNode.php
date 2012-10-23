<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Dav;

use BaseX\Dav\Node;
use BaseX\Helpers as B;
use Sabre_DAV_IFile;

/**
 * WebDAV file node representing a BaseX resource.
 * 
 * @package BaseX
 * 
 */
class ResourceNode extends Node implements Sabre_DAV_IFile
{

  public $mime;
  public $size;
  public $raw;
  
  public function getSize(){
    return $this->raw ? (int)$this->size : 0;
  }
  
  public function isRaw()
  {
    return $this->raw;
  }
  
  public function getContentType(){
    return $this->mime;
  }
  
  public function put($data){
    if($this->raw)
      $this->db->store($this->path, $data);
    else
      $this->db->replace($this->path, $data);
  }

  public function get(){
    return fopen(B::uri($this->db, $this->path), 'r');
  }

  public function getETag() {
    return sprintf('"%s/%s/%s"', $this->db, $this->path, $this->modified);
  }
}
