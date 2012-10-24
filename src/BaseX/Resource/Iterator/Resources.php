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
 * Iterator for basex resources.
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
   * Sort resources by size.
   * 
   * @return Resources
   */
  public function bySize()
  {
    $this->sort = 'size';
    return $this;
  }

  /**
   * Sort resources by modification time.
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
   * Sort resources by content type.
   * 
   * @return Resources
   */
  public function byContentType()
  {
    $this->sort = 'mime';
    return $this;
  }

  /**
   * Sort resources by path.
   * 
   * @return Resources
   */
  public function byPath()
  {
    $this->sort = 'path';
    return $this;
  }

  /**
   * Sort by resource type (raw|xml).
   * 
   * @return Resources
   */
  public function byType()
  {
    $this->sort = 'type';
    return $this;
  }
  
  /**
   * Get the callable to use for converting array resource data to objects.
   * 
   * @return callable
   */
  public function getDenormalizer()
  {
    return null === $this->denormalizer ? 
      array($this, 'denormalize') : $this->denormalizer;
  }
  
  /**
   * Set the callable to use for converting array resource data to objects.
   * 
   * @return callable
   */
  public function setDenormalizer($denormalizer)
  {
    $this->denormalizer = $denormalizer;
    return $this;
  }
  
  protected function processIterator()
  {
    $base = new ListCommand($this->db, $this->path);
    $resources = $base->getIterator();

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
  
  /**
   * Default converter from array to resource object.
   * 
   * @param array $resource
   * @return Document|Raw
   */
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

