<?php

/**
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\DAV;

use BaseX\Database;
use BaseX\DAV\NodeMapper;
use BaseX\DAV\ResourceNode;
use BaseX\Helpers as B;
use Sabre_DAV_ObjectTree;
use Sabre_DAV_Exception_NotFound;

/**
 * ObjectTree for accessing a BaseX\Database via a DAVServer
 *
 * @package BaseX 
 * @author alxarch
 */
class ObjectTree extends Sabre_DAV_ObjectTree
{
  /**
   *
   * @var \BaseX\Resource\Tree
   */
  private $tree;
  
  /**
   *
   * @var \BaseX\DAV\NodeMapper
   */
  private $mapper;
  
  
  /**
   *
   * @var \BaseX\Database
   */
  protected $db;
  
  /**
   *
   * @var string
   */
  protected $dir;
  /**
   *
   * @var string
   */
  protected $root;

  public function __construct(Database $db, $root='', $dir=false)
  {
    $this->db = $db;
    $this->dir = $dir;
    $this->root = $root;
  }
  
  public function getRoot()
  {
    return $this->root;
  }

  /**
   * 
   * @return \BaseX\DAV\NodeMapper
   */
  public function getNodeMapper()
  {
    if(null === $this->mapper)
    {
      $this->mapper = new NodeMapper($this);
    }
    
    return $this->mapper;
  }
  
  /**
   * 
   * @return \BaseX\Resource\Tree
   */
  public function getTree()
  {
    if(null === $this->tree)
    {
      $this->tree = $this->db->getTree($this->root);
      $this->tree->setResourceMapper($this->getNodeMapper());
    }
    
    return $this->tree;
  }

  public function getChildren($path)
  {
    return $this->getTree()->getChildren($path);
  }
  
  /**
   * 
   * @param string $path
   * @return string
   * @throws \InvalidArgumentException If no streamable resource exists at $path.
   */
  public function getURI(ResourceNode $node)
  {
    return B::uri($this->getDatabase(), $node->path);
  }
  
  public function getNodeForPath($path)
  {
    $node = $this->getTree()->getChild($path);
    
    if(null === $node)
    {
      throw new Sabre_DAV_Exception_NotFound('Could not find node at path: ' . $path);
    }
    
    return $node;
  }

  /**
   * Moves a file from one location to another
   *
   * @param string $sourcePath The path to the file which should be moved
   * @param string $destinationPath The full destination path, so not just the destination parent node
   */
  public function move($sourcePath, $destinationPath) 
  {
    $tree = $this->getTree();
    
    $source = $tree->getPath($sourcePath);
    $dest = $tree->getPath($destinationPath);
    
    $this->getDatabase()->rename($source, $dest);
    
    $this->markDirty($sourcePath);
    $this->markDirty($destinationPath);
  }

  public function delete($path)
  {
    $this->getDatabase()->delete($this->getTree()->getPath($path));
    $this->markDirty($path);
  }

  public function copy($sourcePath, $destinationPath)
  {
    $tree = $this->getTree();
    
    $source = $tree->getPath($sourcePath);
    $dest = $tree->getPath($destinationPath);
    
    $this->getDatabase()->copy($source , $dest);
    
    $this->markDirty($destinationPath);
  }
  
  public function markDirty($path)
  {
    $this->tree = null;
  }
  
  public function nodeExists($path)
  {
    $path = trim($path, ' /');
    return $path && $this->getTree()->hasChild($path);
  }
  
  /**
   * 
   * @return string
   */
  public function getDirectory()
  {
    return $this->dir;
  }
  
  /**
   * 
   * @return \BaseX\Database
   */
  public function getDatabase()
  {
    return $this->db;
  }
  
  protected function detectMethod($path)
  {
    $method = 'store';
    $db = $this->getDatabase();
    
    $path = $this->getTree()->getPath($path);
    
    $existing = $db->getResource($path);
    
    if(null === $existing )
    {
      $name = basename($path);
      if($db->getSession()->getInfo()->refresh()->matchesCreatefilter($name))
      {
        $method = 'replace';
      }
    }
    
    return $method;
   
  }


  public function addNode($path, $data=null, $method=null)
  {
    if(null === $method)
    {
      $method = $this->detectMethod($path);
    }
    
    $dest = $this->getTree()->getPath($path);
    
    $this->db->{$method}($dest, $data);
    
    $this->markDirty(B::dirname($path));
    
    $new = $this->getTree()->getChild($path);
    
    return null === $new ? null : $new->getEtag();

  }
}
