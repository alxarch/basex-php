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
use BaseX\Resource\ResourceInterface;

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

  public function getModified()
  {
    if (null === $this->modified)
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
   * @param array
   * @return array
   */
  public function getChildren()
  {
    $children = array();
    $resources = $this->db->getResources($this->path);
    foreach ($resources as $resource)
    {
      $rel = B::relative($resource->path, $this->path);
      if (false === $rel)
      {
        continue;
      }
      $pos = strpos($rel, '/');
      if (false === $pos)
      {
        $children[$rel] = $resource;
        continue;
      }

      $name = substr($rel, 0, $pos);
      if (!isset($children[$name]))
      {
        $children[$name] = new Collection($this->db, B::path($this->path, $name));
      }
    }

    return $children;
  }

  /**
   * 
   * @param string $path
   * @return ResourceInterface|null
   */
  public function getChild($path)
  {
    return $this->db->getResource($this->getRelativePath($path));

    return null;
  }

  /**
   * 
   * @param string $path
   * @return boolean
   */
  public function hasChild($path)
  {
    if ($this->deleted !== true)
    {
      return $this->db->exists($this->getRelativePath($path));
    }

    return false;
  }

  /**
   * Converts an absolute path to relative.
   * 
   * @param string $path The path to convert
   * @return string The converted path or null if not a subpath.
   */
  public function getRelativePath($path)
  {
    return B::relative($path, $this->getPath());
  }

  public function getChildPath($path)
  {
    return B::path($this->getPath(), $path);
  }

  public function refresh()
  {
    $this->modified = null;
    $this->getModified();
    return $this;
  }


}

