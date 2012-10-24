<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource\Iterator;

use ArrayIterator;
use BaseX\Database;
use BaseX\Iterator\ArrayWrapper;
use BaseX\Iterator\CallbackFilter;
use BaseX\Iterator\CallbackParser;
use BaseX\Iterator\Reverse;
use BaseX\Resource\Document;
use BaseX\Resource\Iterator\Exclude;
use BaseX\Resource\Iterator\ListCommand;
use BaseX\Resource\Iterator\Modified;
use BaseX\Resource\Iterator\Resources;
use BaseX\Resource\Iterator\SortResource;
use BaseX\Resource\Raw;

/**
 * Description of Resources
 *
 * @author alxarch
 */
class Resources extends ArrayWrapper
{

  /**
   *
   * @var Database
   */
  protected $db;

  /**
   *
   * @var string
   */
  protected $path;

  /**
   *
   * @var boolean
   */
  protected $modified;

  /**
   *
   * @var string
   */
  protected $sort;

  /**
   *
   * @var array
   */
  protected $exclude = array();
  
  protected $denormalizer;
  protected $mime;
  protected $type;

  public function __construct(Database $db, $path = '')
  {
    $this->db = $db;
    $this->path = $path;
    parent::__construct();
  }

  public function setPath($path)
  {
    $this->path = $path;
    return $this;
  }

  public function exclude($pattern, $type = Exclude::FILTER_REGEX)
  {
    $this->exclude[$pattern] = $type;
    return $this;
  }
  
  public function raw()
  {
    $this->type = 'raw';
    return $this;
  }
  
  public function all()
  {
    $this->type = null;
    return $this;
  }
  
  public function xml()
  {
    $this->type = 'xml';
    return $this;
  }
  
  public function mime($mime)
  {
    if(null === $mime)
    {
      $this->mime = array();
    }
    else
    {
      $this->mime[] = $mime;
    }
    
    return $this;
  }
  
  public function unordered()
  {
    $this->sort = null;
    return $this;
  }

  public function withTimestamps()
  {
    $this->modified = true;
    return $this;
  }
  
  public function withoutTimestamps()
  {
    $this->modified = false;
    return $this;
  }

  /**
   * 
   * @return Resources
   */
  public function bySize()
  {
    $this->sort = 'size';
    return $this;
  }

  /**
   * 
   * @return Resources
   */
  public function byModified()
  {
    $this->modified = true;
    $this->sort = 'modified';
    return $this;
  }

  /**
   * 
   * @return Resources
   */
  public function byContentType()
  {
    $this->sort = 'mime';
    return $this;
  }

  /**
   * 
   * @return Resources
   */
  public function byPath()
  {
    $this->sort = 'path';
    return $this;
  }

  /**
   * 
   * @return Resources
   */
  public function byType()
  {
    $this->sort = 'type';
    return $this;
  }
  
  public function getDenormalizer()
  {
    return null === $this->denormalizer ? 
      array($this, 'denormalize') : $this->denormalizer;
  }
  
  public function setDenormalizer($denormalizer)
  {
    $this->denormalizer = $denormalizer;
    return $this;
  }

  public function getInitialIterator()
  {
    $data = $this->db
      ->getSession()
      ->execute("LIST $this->db \"$this->path\"");

    $lines = explode("\n", $data);

    array_shift($lines);
    array_shift($lines);
    array_pop($lines);
    array_pop($lines);
    array_pop($lines);
    
    return new \ArrayObject($lines);;
  }
  
  protected function processIterator()
  {
    $resources = new ListCommand($this->db, $this->path);
    $resources = new CallbackParser($resources, array('\BaseX\Resource', 'parseLine'));
    if(null !== $this->modified)
    {
      $resources = new Modified($resources, $this->db, $this->path);
    }
    
     if(null !== $this->type)
    {
      $type = $this->type;
      $resources = new CallbackFilter($resources, function($resource) use ($type){
        return $resource['type'] === $type;
      });
    }
    
    if(count($this->mime))
    {
      $mime = $this->mime;
      $resources = new CallbackFilter($resources, function($resource) use ($mime){
        return in_array($resource['mime'], $mime);
      });
    }
    
    if(count($this->exclude) > 0)
    {
      $resources = new Exclude($resources);
      foreach ($this->exclude as $pattern => $type)
      {
        $resources->addFilter($pattern, $type);
      }
    }
    
    if(null !== $this->sort)
    {
      $resources = new SortResource($resources, $this->sort);
    }
    
    $resources = new CallbackParser($resources, $this->getDenormalizer());
    
    if($this->reverse === true)
    {
      $resources = new Reverse($resources);
    }
    
    return $resources;
  }
  
  public function denormalize($resource)
  {
    if ($resource['type'] === 'raw')
    {
      $object = new Raw($this->db, $resource['path']);
    }
    else
    {
      $object = new Document($this->db, $resource['path']);
    }

    $object->setSize((int) $resource['size']);
    $object->setContentType($resource['mime']);

    if (isset($resource['modified']))
    {
      $object->setModified($resource['modified']);
    }

    return $object;
  }
  
  public static function begin(Database $db)
  {
    $class = get_called_class();
    return new $class($db, '');
  }
}

