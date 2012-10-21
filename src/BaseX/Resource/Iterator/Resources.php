<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource\Iterator;

use BaseX\Database;
use BaseX\Resource\Iterator\Exclude;
use BaseX\Resource\Document;
use BaseX\Resource\Raw;

/**
 * Description of Resources
 *
 * @author alxarch
 */
class Resources implements \IteratorAggregate
{

  /**
   *
   * @var BaseX\Database;
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
  protected $reverse=false;


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

  public function __construct(Database $db, $path = '')
  {
    $this->db = $db;
    $this->path = $path;
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

  public function withTimestamps()
  {
    $this->modified = true;
    return $this;
  }

  /**
   * 
   * @return \BaseX\Resource\Iterator\Resources
   */
  public function bySize()
  {
    $this->sort = 'size';
    return $this;
  }

  /**
   * 
   * @return \BaseX\Resource\Iterator\Resources
   */
  public function byModified()
  {
    $this->modified = true;
    $this->sort = 'modified';
    return $this;
  }

  /**
   * 
   * @return \BaseX\Resource\Iterator\Resources
   */
  public function byContentType()
  {
    $this->sort = 'mime';
    return $this;
  }

  /**
   * 
   * @return \BaseX\Resource\Iterator\Resources
   */
  public function byPath()
  {
    $this->sort = 'path';
    return $this;
  }

  /**
   * 
   * @return \BaseX\Resource\Iterator\Resources
   */
  public function byType()
  {
    $this->sort = 'type';
    return $this;
  }

  /**
   * 
   * @return \BaseX\Resource\Iterator\Denormalizer
   */
  public function reverse()
  {
    $this->reverse = true;
    return $this;
  }
  
  public function getDenormalizer()
  {
    return $this->denormalizer;
  }
  
  public function setDenormalizer($denormalizer)
  {
    $this->denormalizer = $denormalizer;
    return $this;
  }

  /**
   * 
   * @return \ArrayIterator
   */
  public function getIterator()
  {
    $base = new ListCommand($this->db, $this->path);
    $resources = new Callback($base, array('\BaseX\Resource', 'parseLine'));
    
    if($this->modified)
    {
      $resources = new Modified($resources, $this->db, $this->path);
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
      $resources = new Sort($resources, $this->sort);
    }
    
    $converter = null === $this->denormalizer ? array($this, 'denormalize') : $this->denormalizer;
    
    $result = new Callback($resources, $converter);
    
    return new \ArrayIterator(iterator_to_array($result));
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
  
  public function getFirst()
  {
    $iter = $this->getIterator();
    
    return $iter->count() > 0 ? $iter->offsetGet(0) : null;
  }
  
  public function getLast()
  {
    $iter = $this->getIterator();
    $total = $iter->count();
    return $total > 0 ? $iter->offsetGet($total - 1) : null;
  }
  
  public function getSingle()
  {
    $iter = $this->getIterator();
    return $iter->count() === 1 ? $iter->offsetGet(0) : null;
  }
}

