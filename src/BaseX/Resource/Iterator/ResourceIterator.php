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
use BaseX\Resource\Document;
use BaseX\Resource\Raw;
use BaseX\Resource;
use \Iterator;
use \Countable;
use BaseX\Query\QueryBuilder;
use BaseX\Query\Results\DateTimeResults;

/**
 * Description of ResourceIterator
 *
 * @author alxarch
 */
class ResourceIterator implements Iterator, Countable, \ArrayAccess
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
   * @var \ArrayObject
   */
  protected $lines;
  
  /**
   *
   * @var array
   */
  protected $resources;

  /**
   *
   * @var array
   */
  protected $idx;

  /**
   *
   * @var \BaseX\Query\Results\DateTimeResults
   */
  protected $timestamps;
  
  /**
   *
   * @var boolean
   */
  protected $modified;

  public function __construct(Database $db, $path = '', $modified = true)
  {
    $this->db = $db;
    $this->path = $path;
    $this->modified = $modified;
  }

  protected function getTimestamps()
  {
    if (null === $this->timestamps)
    {
      $xql = sprintf("db:list-details('%s', '%s')/@modified-date/string()", $this->db, $this->path);
      $this->timestamps = QueryBuilder::begin()
        ->setBody($xql)
        ->getQuery($this->db->getSession())
        ->getResults(new DateTimeResults());
    }

    return $this->timestamps;
  }

  protected function getLines()
  {
    if (null === $this->lines)
    {
      $data = $this->db->getSession()->execute("LIST $this->db \"$this->path\"");

      $lines = explode("\n", $data);

      array_shift($lines);
      array_shift($lines);
      array_pop($lines);
      array_pop($lines);
      array_pop($lines);
      
      $this->lines = new \ArrayObject(array_values($lines));

      $this->resources = array();
      $this->matches = array();

      $this->idx = empty($lines) ? array() : range(0, count($lines) - 1);
    }
   
    return $this->lines;
  }

  protected function asObject($matches)
  {
    if ($matches['type'] === 'raw')
    {
      $resource = new Raw($this->db, $matches['path']);
    }
    else
    {
      $resource = new Document($this->db, $matches['path']);
    }

    $resource->setSize((int) $matches['size']);
    $resource->setContentType($matches['content_type']);

    if (isset($matches['modified']))
    {
      $resource->setModified($matches['modified']);
    }
    return $resource;
  }

  public function get($i)
  {
    if (!$this->getLines()->offsetExists($i))
    {
      return null;
    }

    if (!isset($this->resources[$i]))
    {
      $resource = Resource::parseLine($this->getLines()->offsetGet($i));

      if ($this->modified)
      {
        $resource['modified'] = $this->getTimestamps()->offsetGet($i);
      }
      else
      {
        $resource['modified'] = null;
      }

      $resource['object'] = $this->asObject($resource);
      $this->resources[$i] = $resource;

    }

    return $this->resources[$i]['object'];
  }

  public function current()
  {
    return $this->get($this->key());
  }

  public function key()
  {
    $this->getLines();
    return current($this->idx);
  }

  public function next()
  {
    $this->getLines();
    next($this->idx);
  }

  public function rewind()
  {
    $this->getLines();
    reset($this->idx);
  }

  public function valid()
  {
    $this->getLines();
    return false !== current($this->idx);
  }

  public function count()
  {
    return $this->getLines()->count();
  }

  /**
   * 
   * @return \BaseX\Resource
   */
  public function getFirst()
  {
    $this->getLines();
    return $this->get($this->idx[0]);
  }

  /**
   * 
   * @return \BaseX\Resource
   */
  public function getLast()
  {
    $this->getLines();
    return $this->get($this->idx[count($this->idx) - 1]);
  }

  /**
   * 
   * @return \BaseX\Resource|null
   */
  public function getSingle()
  {
    return $this->getLines()->count() === 1 ? $this->get($this->idx[0]) : null;
  }

  /**
   * 
   * @param string $key
   * @return \BaseX\Resource\Iterator\ResourceIterator
   */
  protected function sort($key)
  {
    $idx = [];
    
    foreach ($this as $i => $resource)
    {
      $idx[] = $this->resources[$i][$key];
    }
    
    asort($idx);
    
    $this->idx = array_keys($idx);
    
    return $this;
  }
  
  /**
   * 
   * @return \BaseX\Resource\Iterator\ResourceIterator
   */
  public function bySize()
  {
    return $this->sort('size');
  }
  
  
  /**
   * 
   * @return \BaseX\Resource\Iterator\ResourceIterator
   */
  public function byModified()
  {
    return $this->sort('modified');
  }
  
  /**
   * 
   * @return \BaseX\Resource\Iterator\ResourceIterator
   */
  public function byContentType()
  {
    return $this->sort('content_type');
  }
  
  /**
   * 
   * @return \BaseX\Resource\Iterator\ResourceIterator
   */
  public function byPath()
  {
    return $this->sort('path');
  }
  
  /**
   * 
   * @return \BaseX\Resource\Iterator\ResourceIterator
   */
  public function byType()
  {
    return $this->sort('type');
  }
  
  /**
   * 
   * @return \BaseX\Resource\Iterator\ResourceIterator
   */
  public function reverse()
  {
    $this->idx = array_reverse($this->idx);
    return $this;
  }

  public function offsetExists($offset)
  {
    return null !== $this->idx && array_key_exists($offset, $this->idx);
  }

  public function offsetGet($offset)
  {
    return $this->get($offset);
  }

  public function offsetSet($offset, $value)
  {
    throw new ErrorException('Not implemented');
  }

  public function offsetUnset($offset)
  {
    throw new \ErrorException('Not implemented.');
  }

}

