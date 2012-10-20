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

  public function getChildren()
  {
    return $this->tree->getChildren($this->path);
  }

  public function getChild($name)
  {
    return $this->tree->getNodeForPath(B::path($this->path, $name));
  }

  public function childExists($name)
  {
    return $this->tree->nodeExists(B::path($this->path, $name));
  }

  public function createFile($name, $data = null)
  {
    return $this->tree->addNode($this, $name, $data);
  }

  public function createDirectory($name)
  {
    return $this->tree->addNode($this, $name . '/.empty', null, 'store');
  }

  public function getLastModified()
  {
    return time();
  }

}
