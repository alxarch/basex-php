<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Dav;

use Sabre_DAV_ICollection;
use BaseX\Dav\Node;
use BaseX\Helpers as B;

/**
 * WebDAV collection node representing a collection in a BaseX database.
 * 
 * @package BaseX
 * 
 */
class CollectionNode extends Node implements Sabre_DAV_ICollection
{
  public $children;
  
  public function getChildren(){
    if(null === $this->children)
    {
      $this->children = $this->tree->getChildren($this->path);
    }
    return $this->children;
  }

  public function getChild($name){
    if(null === $this->children)
    {
      $this->children = $this->tree->getChildren($this->path);
    }
    
    return isset ($this->children[$name]) ? $this->children[$name] : null;
  }

  public function childExists($name){
    return null === $this->children ? 
      $this->tree->nodeExists(B::path($this->path, $name)) : 
      isset($this->children[$name]);
  }
 
  public function createFile($name, $data = null){
    return $this->tree->addNode($this, $name, $data);
  }

  public function createDirectory($name){
    return $this->tree->addNode($this, $name.'/.empty', null, 'store');
  }
  
  public function getLastModified() 
  {
    if(null === $this->modified)
    {
      $mod = $this->tree->getPathModified($this->path);
      if($mod instanceof \DateTime)
      {
        $this->modified = (int) $mod->format('U');
      }
    }
    return $this->modified;
  }

}
