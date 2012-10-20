<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Dav;

use BaseX\Dav\Node;
use BaseX\Resource;

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
  public $raw;
  
  public function getSize(){
    return $this->size;
  }
  
  public function isRaw()
  {
    return $this->raw;
  }
  
  public function getContentType(){
    return $this->mime;
  }
  
  public function put($data){
    $this->tree->put($this, $data);
  }

  public function get(){
    return $this->tree->get($this);
  }

  public function getETag() {
    return $this->tree->getEtag($this);
  }
  
  public function unserialize($data)
  {
    $xml = @simplexml_load_string($data);
    
    if($xml instanceof \SimpleXMLElement)
    {
      $this->path = $this->tree->getRelativePath((string) $xml);
      $time = Resource::parseDate((string) $xml['modified-date']);
      if($time instanceof \DateTime)
        $this->modified = (int) $time->format('U');
      else
        $this->modified = time();
      
      $this->mime = (string) $xml['content-type'];
      $this->size = isset($xml['size']) ? (int) $xml['size'] : 0;
      $this->raw = 'false' !== (string) $xml['raw'];
    }
  }
}
