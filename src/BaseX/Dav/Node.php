<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Dav;

use Sabre_DAV_INode;
use BaseX\Dav\ObjectTree;
use BaseX\Helpers as B;
/**
 * Description of INode
 *
 * @author alxarch
 */
abstract class Node implements Sabre_DAV_INode
{
  /**
   * @var \BaseX\Dav\ObjectTree
   */
  protected $tree;
  public $path;
  public $modified;
  
  public function __construct(ObjectTree $tree)
  {
    $this->tree = $tree;
  }
  
  public function delete() {
    $this->tree->delete($this->path);
  }
  
  public function getLastModified() {
    return $this->modified;
  }
  
  public function getName(){
    return basename($this->path);
  }

  public function setName($name){
    $dest = B::rename($this->path, $name);
    $this->tree->move($this->path, $dest);
    $this->path = $dest;
    
  }
  
  public function getFullpath(){
    return $this->tree->getFullpath($this->path);
  }
}