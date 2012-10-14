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
use BaseX\Query\Result\SimpleXMLMapperInterface;
use BaseX\Resource\Interfaces\CollectionInterface;
use Serializable;
use BaseX\Error\UnserializationError;
use BaseX\Error\SessionError;
use BaseX\Error;
use BaseX\Database;

/**
 * Resource tree for a BaseX\Database.
 *
 * @author alxarch
 */
class Tree implements Serializable
{
  
  /**
   *
   * @var boolean
   */
  protected $complete;
  
  /**
   *
   * @var \SimpleXMLElement
   */
  protected $tree;
  
  /**
   *
   * @var int
   */
  protected $depth;
  
  /**
   *
   * @var \BaseX\Query\Result\SimpleXMLMapperInterface
   */
  protected $mapper;
  
  /**
   *
   * @var string
   */
  protected $root;
  
  /**
   * Sets a default resource mapper for this tree.
   * 
   * @param \BaseX\Query\Result\SimpleXMLMapperInterface $mapper
   * 
   * @return \BaseX\Resource\Tree
   */
  public function setResourceMapper(SimpleXMLMapperInterface $mapper)
  {
    $this->mapper = $mapper;
    return $this;
  }
  
  /**
   * 
   * @return \BaseX\Query\Result\SimpleXMLMapperInterface
   */
  public function getResourceMapper()
  {
    return $this->mapper;
  }

  /**
   * 
   * @param string $path
   * @param \BaseX\Query\Result\SimpleXMLMapperInterface $mapper
   * @return array
   */
  public function getChildren($path='', SimpleXMLMapperInterface $mapper = null)
  {
    $path = $this->getPath($path);
    $xpath = "//collection[@path = '$path']/child::*";
    
    return $this->getResourcesByXpath($xpath, $mapper);
  }
  
  /**
   * 
   * @param string $path
   * @param \BaseX\Query\Result\SimpleXMLMapperInterface $mapper
   * @return mixed
   */
  public function getChild($path, SimpleXMLMapperInterface $mapper=null)
  {
    $path = $this->getPath($path);
    $xpath = "//collection[@path = '$path']|//resource[text() = '$path']";
    $result =  $this->getResourcesByXpath($xpath, $mapper);
    return count($result) === 1 ? $result[0] : null;
  }
  
  /**
   * 
   * @param string $path
   * @return boolean
   */
  public function hasStreamableChild($path)
  {
    $path = $this->getPath($path);
    $xpath = "//resource[text() = '$path']";
    return count($this->getXML()->xpath($xpath)) === 1;
  }
  
  /**
   * 
   * @param string $path
   * @return boolean
   */
  public function hasChild($path)
  {
    $path = $this->getPath($path);
    $xpath = "//collection[@path = '$path']|//resource[text() = '$path']";
    return count($this->getXML()->xpath($xpath)) === 1;
  }

  /**
   * 
   * @param string $xpath
   * @param \BaseX\Query\Result\SimpleXMLMapperInterface $mapper
   * @return array
   */
  public function getResourcesByXpath($xpath, SimpleXMLMapperInterface $mapper=null)
  {
    $data = $this->getXML()->xpath($xpath);
    
    if(null === $mapper)
    {
      $mapper = $this->getResourceMapper();
    }
    
    if(null === $mapper)
    {
      return $data;
    }
    
    $resources = array();
    
    foreach ($data as $d)
    {
      $resource = $mapper->getResultFromXML($d);
      if(null === $resource)
      {
        continue;
      }
      
      if($resource instanceof CollectionInterface)
      {
        $class = get_called_class();
        $tree = new $class();
        try{
          $tree->unserialize($d);
          $resource->setTree($tree);
        }
        catch (UnserializationError $e){
          ;
        }
      }

      $resources[] = $resource;
    }
    
    return $resources;
    
  }
  
  public function serialize(){
    if(null === $this->tree)
      return null;
    
    return B::stripXMLDeclaration($this->getXML()->asXML());
  }
  
  public function unserialize($data)
  {
    $xml = $data instanceof \SimpleXMLElement ? $data : @simplexml_load_string($data);
    
    if($xml !== false && $xml instanceof \SimpleXMLElement && 
            $xml->getName() === 'collection' && 
            isset($xml['path']) && 
            isset($xml['modified-date']) && 
            isset($xml['complete']) && 
            isset($xml['depth']))
    {
      $this->tree = $xml;
      $this->root = (string) $xml['path'];
      $this->depth = (int) $xml['depth'];
      $this->complete = 'true' === (string) $xml['complete'];
    }
    else
    {
      throw new UnserializationError();
    }
  }

  /**
   * Checks whether data is valid tree data.
   * 
   * @param \SimpleXMLElement $tree
   * @return type
   */
  public static function isValid(\SimpleXMLElement $tree)
  {
  }
  
  public function getDepth(){
    return $this->depth;
  }
  
  /**
   * Check whether current tree data reach specified depth.
   * 
   * @param int $depth
   * @return boolean
   */
  public function reaches($depth)
  {
    return $this->complete || $depth < $this->depth;
  }
  
  /**
   * Whether this tree has loaded all possible subtrees.
   * 
   * @return boolean
   */
  public function isComplete(){
    return $this->complete;
  }
  
  /**
   * The root for this tree.
   * 
   * @return string
   */
  public function getRoot() {
    return $this->root;
  }
  
  /**
   * 
   * @return \SimpleXMLElement
   */
  public function getXML()
  {
    if(null === $this->tree)
    {
      throw new Error('Uninitialized tree.');
    }
    return $this->tree;
  }
  
  /**
   * 
   * @param string $path
   * @param \BaseX\Query\Result\SimpleXMLMapperInterface $mapper
   * @return \BaseX\Resource\Tree|null
   */
  public function getSubTree($path, SimpleXMLMapperInterface $mapper = null)
  {
    $xpath = "//collection[@path = '$path']";
    
    $results = $this->getXML()->xpath($xpath);
    
    if(count($results) === 1)
    {
      $class = get_called_class();
      $subtree = new $class($results[0]);
      if($mapper !== null)
        $subtree->setResourceMapper($mapper);
      return $subtree;
    }
    
    return null;
  }
  
  /**
   * Converts a relative path to full path.
   * 
   * @param string $path
   * @return string
   */
  public function getPath($path)
  {
    return B::path($this->getRoot(), $path);
  }
  
  /**
   * Converts an absolute path to relative.
   * 
   * @param string $path The path to convert
   * @return string The converted path or null if not a subpath.
   */
  public function getRelativePath($path)
  {
    return B::relative($path, $this->getRoot());
  }
  
  public function load(Database $db, $root='', $depth=-1)
  {
            $depth = (int) $depth;
    $xql = <<<XQL
declare function local:relative-path(\$path as xs:string, \$base as xs:string) as xs:string{
      if(\$base ne '') then substring(\$path, string-length(\$base) + 2) else \$path
    };

declare function local:trim-path(\$path as xs:string) as xs:string {
  if(starts-with(\$path, '/')) then substring(\$path, 2) else \$path
};

declare function local:tree(\$db as xs:string, \$path as xs:string, \$depth as xs:integer) as element(collection)*
{
  if (db:exists(\$db, \$path)) then () else
  let \$path := local:trim-path(\$path)
  let \$resources := db:list-details(\$db, \$path)
  let \$modified := max(\$resources/@modified-date/string())
  let \$children := if(\$depth eq 0) then () else
    for \$r in \$resources
      let \$rel-path := local:relative-path(\$r/text(), \$path)
      let \$name := substring-before(\$rel-path, '/')
      group by \$name
      order by \$name
      return if(\$name)
        then local:tree(\$db, \$path||'/'||\$name, \$depth - 1)
        else \$r
            
  return if(empty(\$resources) and \$path ne '') then () else
    <collection>{ 
      attribute path { \$path } ,
      attribute modified-date { \$modified } ,
      attribute complete { \$depth lt 0 } ,
      \$children 
    }</collection>
      
};
    
declare function local:depth(\$n as element(collection), \$max as xs:integer)
{
  max(if(\$n/collection) then for \$c in \$n/collection return local:depth(\$c, \$max + 1) else \$max)
};

copy \$tree := local:tree('$db', '$root', $depth)
modify (
  for \$c in \$tree/descendant-or-self::collection
    return insert node attribute depth {local:depth(\$c, 0)} into \$c
)
return \$tree

XQL;

    try{
      $data = $db->getSession()->query($xql)->execute();
    }
    catch (SessionError $e)
    {
      throw new Error('Path does not exist.');
    }
    $this->unserialize($data);
    
  }
}

