<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Query\Result\SimpleXMLMapperInterface;
use BaseX\Query\Result\MapperInterface;
use BaseX\Resource\Interfaces\CollectionInterface;
use BaseX\Resource;
//use BaseX\Query\Result\SimpleXMLMapper;
//use BaseX\Helpers as B;
use BaseX\Resource\Tree;

/**
 * BaseX Collection
 *
 * @author alxarch
 */
class Collection extends Resource implements CollectionInterface
{
  /**
   *
   * @var \BaseX\Database\Tree
   */
  protected $tree;

  /**
   * Reloads collection info.
   * 
   * @return \BaseX\Collection $this
   */
  public function refresh() 
  {
    $db = $this->getDatabase();
    $path = $this->getPath();
    $xql = "max(db:list-details('$db', '$path')//@modified-date/string())";
    $result = $db->getSession()->query($xql)->execute();
    if($result)
    {
      $this->modified = new \DateTime($result);
    }
    else
    {
      $this->path = false;
      $this->modified = false;
      $this->db = null;
    }

    return $this;
  }
  
  /**
   * Gets all resources for this collection.
   * 
   * @param string $path list resources from this subpath
   * @return array A BaseX\Resource array
   */
  public function getResources($path=null, MapperInterface $mapper=null)
  {
    return $this->getDatabase()->getResources($this->getSubpath($path), $mapper);
  }

  /**
   * Checks collection contents for a child.
   * 
   * @param string $name
   * @return boolean
   */
  public function hasChild($name)
  {
    return $this->getTree()->hasChild($name);
  }
  
  public function getSubpath($path)
  {
    return trim($this->getPath().'/'.$path, '/');
  }

//  public function addChild($name, $data, $raw='detect') 
//  {
//    $data = $this->getTree()->getChild($name, new SimpleXMLMapper());
//    if('collection' === $data->getName())
//    {
//      throw new Error('A collection with the same name exists.');
//    }
//    
//    if($raw === 'detect')
//    {
//      if(null === $data)
//      {
//        $createfilter = $this->getDatabase()->getSession()->getInfo()->option('createfilter');
//        foreach(explode(',', $createfilter) as $pattern)
//        {
//          if(fnmatch($pattern, $name))
//          {
//            $raw = true;
//          }
//        }
//      }
//    }
//    $uri = B::uri($this->getDatabase(), $this->getSubpath($name));
//    
//    $dest = fopen($uri, 'w');
//    
//    if(is_resource($data))
//    {
//      stream_copy_to_stream($data, $dest);
//    }
//    else
//    {
//      fwrite($dest, $data);
//    }
//    
//    fclose($dest);
    
//    
//    $this->getSession()->execute('OPEN '. $this->getDatabase());
//    
//    if($raw)
//    {
//      $this->getSession()->store($path, $data);
//    }
//    else
//    {
//      $this->getSession()->replace($path, $data);
//    }
//    
//    return $this;
//  }
  
  public function rename($name)
  {
    parent::rename($name);
    $this->tree = null;
    
  }
  
  public function delete() 
  {
    parent::delete();
    $this->tree = null;
  }
   
  public function getChildren(SimpleXMLMapperInterface $mapper = null)
  {
    return $this->getTree()->getChildren('', $mapper);
  }
  
  public function setTree(Tree $tree)
  {
    $this->tree = $tree;
    return $this;
  }

  /**
   * 
   * @param int $mindepth
   * @return \BaseX\Resource\Tree
   */
  public function getTree($mindepth=1)
  {
    // Minimize unneeded tree reloading.
    
    if(null === $this->tree || !$this->tree->reaches($mindepth)) 
    {
      $this->tree = $this->getDatabase()->getTree($this->getPath(), $mindepth);
    }
    
    return $this->tree;
  }

}