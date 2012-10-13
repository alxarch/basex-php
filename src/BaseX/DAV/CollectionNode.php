<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\DAV;

use BaseX\Helpers as B;
use BaseX\DAV\ObjectTree;
use Sabre_DAV_ICollection;

/**
 * WebDAV collection node representing a collection in a BaseX database.
 * 
 * @package SabreDAV-BaseX
 * 
 */
class CollectionNode implements Sabre_DAV_ICollection
{
  /**
   *
   * @var string
   */
  public $path;
  
  /**
   *
   * @var int
   */
  public $modified;
  
  /**
   *
   * @var string
   */
  public $etag;
  
  /**
   *
   * @var \BaseX\DAV\ObjectTree
   */
  protected $tree;
  
  public function __construct(ObjectTree $tree, $path) {
    $this->tree = $tree;
    $this->path = $path;
  }

  public function getName() {
    return basename($this->path);
  }
  
  public function delete(){
    $this->tree->delete($this->path);
  }
  
  public function getChildren()
  {
    return $this->tree->getChildren($this->path);
  }

  public function getChild($name){
    return $this->tree->getNodeForPath(B::path($this->path, $name));
  }

  public function childExists($name) 
  {
    return $this->tree->nodeExists(B::path($this->path, $name));
  }

  public function createFile($name, $data = null) 
  {
    return $this->tree->addNode(B::path($this->path, $name), $data);
  }

  public function createDirectory($name){
    
    $node = new ResourceNode($this->tree);
    $node->path = B::path($this->path, $name);
    $this->tree->addNode($node);
  }

  public function getLastModified(){
    return $this->modified;
  }

  public function setName($name)
  {
    $this->tree->move($this->path, B::rename($this->path, $name));
  }
}
