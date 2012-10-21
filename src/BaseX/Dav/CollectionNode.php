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
use BaseX\Dav\Iterator\Nodes;
use \Sabre_DAV_Exception_NotFound;

/**
 * WebDAV collection node representing a collection in a BaseX database.
 * 
 * @package BaseX
 * 
 */
class CollectionNode extends Node implements Sabre_DAV_ICollection
{
  
  protected $iterator;

  public function getChildren()
  {
    $children = array();

    foreach ($this->getNodes() as $node)
    {
      $rel = B::relative($node->path, $this->path);
      if (false === $rel)
      {
        continue;
      }
      $pos = strpos($rel, '/');
      if (false === $pos)
      {
        $children[$rel] = $node;
        continue;
      }

      $name = substr($rel, 0, $pos);
      if (!isset($children[$name]))
      {
        $children[$name] = new CollectionNode($this->db, B::path($this->path, $name));
      }
    }

    return $children;
  }
  
  protected function getNodes($path='')
  {
    return Nodes::begin($this->db)
      ->setPath(B::path($this->path, $path))
      ->withTimestamps()
      ->getIterator()
      ;
  }

  public function getChild($name)
  {
    $nodes = $this->getNodes($name);
    $path = B::path($this->path, $name);
    switch (count($nodes))
    {
      case 0:
        throw new Sabre_DAV_Exception_NotFound;
        break;
      case 1:
        $node = $nodes->getFirst();
        if ($node->path === $path)
          return $node;
        else
          return new CollectionNode($this->db, $path);
        break;
      default:
        return new CollectionNode($this->db, $path);
        break;
    }
  }

  public function childExists($name)
  {
    return $this->db->exists(B::path($this->path, $name));
  }

  public function createFile($name, $data = null)
  {
    $path = B::path($this->path, $name);
    
    if($this->db->getSession()->getInfo()->matchesCreatefilter($name))
      $this->db->replace ($path, $data);
    else
      $this->db->store ($path, $data);
    
    $node = $this->getNodes($name)->getSingle();
    
    return $node->getEtag();
  }

  public function createDirectory($name)
  {
    return $this->db->store(B::path($this->path, $name, '.empty'), '');
  }

  public function getLastModified()
  {
    return time();
  }

}
