<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Helpers as B;
use BaseX\Resource;
use BaseX\Resource\CollectionInterface;
use BaseX\Resource\Tree;

/**
 * Resource tree for a BaseX\Database.
 *
 * @author alxarch
 */
class Collection extends Resource implements CollectionInterface
{
  /**
   *
   * @var array
   */
  protected $children;

  public function getModified() {
    if(null === $this->modified)
    {
      $this->modified = $this->db
        ->getResources($this->path)
        ->byModified()
        ->reverse()
        ->getFirst()
        ->getModified();
    }
    
    return $this->modified;
  }
  
  /**
   * 
   * @param string $path
   * @param \BaseX\Query\Results\SimpleXMLMapperInterface $mapper
   * @return array
   */
  public function getChildren()
  {
    if(null === $this->children)
    {
      $this->children = $this->getTree()->rebuild()->offsetGet('/');
    }
    
    return $this->children;
  }
  
  protected function getTree()
  {
    $that = $this;
    return Tree::make($this->getPath())
      ->setMaxdepth(0)
      ->setItemLoader(function($path) use ($that){
        $items = array();
        foreach ($that->getDatabase()->getResources($path) as $r)
        {
          $items[$r->getPath()] = $r;
        }
        return $items;
      })
      ->setTreeConverter(function(Tree $tree) use ($that){
          
          $class = get_class($that);
          
          $children = array();
        
          foreach ($tree->getChildren() as $name => $child)
          {
            if($child instanceof Tree)
            {
              $children[$name] = new $class($that->getDatabase(), $child->getRoot());
            }
            else
            {
              $children[$name] = $child;
            }
          }
          
          return $children;
      });
  }

  /**
   * 
   * @param string $path
   * @return \BaseX\Resource\ResourceInterface|null
   */
  public function getChild($path)
  {
    if($this->hasChild($path))
    {
      return $this->children[$path];
    }
    
    return null;
  }
  
  /**
   * 
   * @param string $path
   * @return boolean
   */
  public function hasChild($path)
  {
    if($this->deleted !== true)
    {
      $this->getChildren();
      return is_array($this->children) && isset($this->children[$path]);
    }
    
    return false;
  }

  /**
   * Converts an absolute path to relative.
   * 
   * @param string $path The path to convert
   * @return string The converted path or null if not a subpath.
   */
  public function getRelativePath($path){
    return B::relative($path, $this->getPath());
  }

  public function getChildPath($path){
    return B::path($this->getPath(), $path);
  }
  
  public function refresh()
  {
    $this->modified = null;
    $this->children = null;
    $this->getChildren();
    $this->getModified();
    return $this;
  }

  public function offsetExists($offset) 
  {
    return $this->hasChild($offset);
  }

  public function offsetGet($offset) 
  {
    return $this->getChild($offset);
  }

  public function offsetSet($offset, $value)
  {
    throw new \RuntimeException('Not implemented.');
  }

  public function offsetUnset($offset)
  {
    $this->db->delete($this->getChildPath($offset));
  }

}

