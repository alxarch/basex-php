<?php

/**
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Dav;

use Query\Result\DateTimeResults;
use BaseX\Database;
use BaseX\Dav\ResourceNode;
use BaseX\Dav\ResourceNodeResults;
use BaseX\Helpers as B;
use Sabre_DAV_ObjectTree;
use Sabre_DAV_Exception_NotFound;
use BaseX\Resource\Tree;
use BaseX\Query\QueryBuilder;
use BaseX\Resource;

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

  protected $cache;

  public function __construct(Database $db, $root='', $dir=false)
  {
    $this->db = $db;
    $this->root = $root;
    $this->tree = Tree::make($root)
            ->setMaxdepth(-1)
            ->setItemLoader(array($this, 'loadItems'))
            ->rebuild();
    
    $this->dir = $dir;
  }
  
  public function loadNodes($path)
  {
    $path = B::path($this->root, $path);
    $xql = "db:list-details('$this->db', '$path')";
    return QueryBuilder::begin()
            ->setBody($xql)
            ->getQuery($this->db->getSession())
            ->getResults(new ResourceNodeResults($this));
    
  }
  public function loadItems($path)
  {
    $results = $this->loadNodes($path);
    
    $items = array();
    
    foreach ($results as $r)
    {
      $items[$r->getFullpath()] = $r;
    }
    
    return $items;
  }
  
  public function getPathModified($path)
  {
    $path = $this->getRelativePath($path);
    $xql = "max(db:list-details('$this->db', '$path')/@modified-date/string())";
    return QueryBuilder::begin()
            ->setBody($xql)
            ->getQuery($this->db->getSession())
            ->getResults(new DateTimeResults(Resource::DATE_FORMAT))
            ->getSingle();
  }
  
  public function getFullpath($path){
    return B::path($this->root, $path);
  }
  
  public function getRelativePath($path){
    return B::relative($path, $this->root);
  }

  public function getChildren($path)
  {
    $node = $this->tree[$path];
    
    if($node instanceof Tree)
    {
      $children = array();
      
      foreach ($node->getChildren() as $name => $child) 
      {
        if($child instanceof Tree)
        {
          $col =  new CollectionNode($this);
          $col->path = $this->getRelativePath($child->getRoot());
          $children[$name] = $col;
        }
        else
        {
          $children[$name] = $child;
        }
      }
      
      return $children;
    }
    
    throw new Sabre_DAV_Exception_NotFound('Could not find node at path: ' . $path);
  }
  
  /**
   * 
   * @param string $path
   * @return string
   * @throws \InvalidArgumentException If no streamable resource exists at $path.
   */
  public function getURI(ResourceNode $node)
  {
    if($this->dir && $node->resource)
    {
     B::path($this->dir, $node->path);
    }
    
    return B::uri($this->db, $this->getFullpath($node->path));
  }
  
  public function put(ResourceNode $node, $data)
  {
    $dest = $this->getFullpath($node->path);
    
    if($node->resource === true)
    {
      if($this->dir)
      {
        $out = fopen($this->getURI($node), 'w');
        if(is_resource($data))
        {
          while (!feof($data))
          {
            fwrite($out, fread($data, 65536));
          }
        }
        else 
        {
          fwrite($out, $data);
        }
        fclose($out);
      }
      else 
      {
         $this->db->store($dest, $data);
      }
    }
    else
    {
      $this->db->replace($dest, $data);
    }
    
    $this->markDirty($node->path);
  }
  
  /**
   * 
   * @param type $path
   * @return \BaseX\Dav\ResourceNode|\BaseX\Dav\CollectionNode
   * @throws Sabre_DAV_Exception_NotFound
   */
  public function getNodeForPath($path)
  {
    $node = $this->tree[$path];
    
    if(null === $node)
    {
      throw new Sabre_DAV_Exception_NotFound('Could not find node at path: ' . $path);
    }
   
    if($node instanceof Tree)
    {
      $c =  new CollectionNode($this);
      $c->path = $this->getRelativePath($node->getRoot());
      return $c;
    }
    
    return $node;
  }

  /**
   * Moves a file from one location to another
   *
   * @param string $sourcePath The path to the file which should be moved
   * @param string $destinationPath The full destination path, so not just the destination parent node
   */
  public function move($source, $dest) 
  {
    $source = $this->getFullpath($source);
    $dest = $this->getFullpath($dest);
    
    $this->db->rename($source, $dest);
    
    $this->markDirty($source);
    $this->markDirty($dest);
  }

  public function delete($path)
  {
    $this->db->delete($this->getFullpath($path));
    $this->markDirty($path);
  }

  public function copy($source, $dest)
  {
    $source = $this->getFullpath($source);
    $dest = $this->getFullpath($dest);
    
    $this->db->copy($source , $dest);
    
    $this->markDirty($dest);
  }
  
  public function markDirty($path)
  {
    unset($this->tree[$path]);
  }
  
  public function nodeExists($path)
  {
    $path = trim($path, '/');
    return $path !== '' && isset($this->tree[$path]);
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
    
    $name = basename($path);
    $info = $this->db->getSession()->getInfo();
    if($info->refresh()->matchesCreatefilter($name))
    {
      return 'replace';
    }
    
    return 'store';
   
  }
  
  public function getEtag(ResourceNode $node)
  {
    return sprintf('"%s/%s/%s/%s"', $this->db, $this->root, $node->path, $node->modified);
  }

  public function addNode(CollectionNode $collection, $name, $data, $method=null)
  {
    $newpath = B::path($collection->path, $name);
    
    if(null === $method)
    {
      $method = $this->detectMethod($newpath);
    }
    
    $dest = $this->getFullpath($newpath);
    
    $this->db->{$method}($dest, $data);
    
    $this->markDirty($collection->path);
   
    $new = $this->loadNodes($newpath)->getSingle();
    
    return null === $new ? null : $this->getEtag($new);

  }
  
}
