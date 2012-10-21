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
use BaseX\Query\QueryBuilder;
use BaseX\Query\Results\DateTimeResults;

/**
 * Description of ResourceIterator
 *
 * @author alxarch
 */
class ResourceIterator implements \Iterator, \Countable, \ArrayAccess
{

  const FILTER_GLOB = 1;
  const FILTER_REGEX = 2;
  const FILTER_NAME_GLOB = 4;
  const FILTER_NAME_REGEX = 8;

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
  protected $reverse;

  /**
   *
   * @var array
   */
  protected $resources;

  /**
   *
   * @var \ArrayIterator
   */
  protected $idx;

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
  protected $filters = array();

  public function __construct(Database $db, $path = '', $modified = true)
  {
    $this->db = $db;
    $this->modified = $modified;
    $this->setPath($path);
  }

  public function setPath($path)
  {
    $this->path = $path;
    return $this;
  }

  public function reload()
  {
    $data = $this->db->getSession()->execute("LIST $this->db \"$this->path\"");

    $lines = explode("\n", $data);
    array_shift($lines);
    array_shift($lines);
    array_pop($lines);
    array_pop($lines);
    array_pop($lines);

    if ($this->modified)
    {
      $xql = sprintf("db:list-details('%s', '%s')/@modified-date/string()", $this->db, $this->path);
      $timestamps = QueryBuilder::begin()
        ->setBody($xql)
        ->getQuery($this->db->getSession())
        ->getResults(new DateTimeResults());
    }

    $this->resources = new \ArrayObject(array());

    foreach (array_values($lines) as $i => $line)
    {
      $resource = Resource::parseLine($line);

      if ($this->modified)
      {
        $resource['modified'] = $timestamps[$i];
      }
      else
      {
        $resource['modified'] = null;
      }

      $this->resources->append($resource);
    }

    $this->idx = null;

    return $this;
  }

  /**
   * 
   * @return \ArrayObject
   */
  protected function getResources()
  {
    if (null === $this->resources)
    {
      $this->reload();
    }

    return $this->resources;
  }

  protected function getObject($resource)
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
    $object->setContentType($resource['content_type']);

    if (isset($resource['modified']))
    {
      $object->setModified($resource['modified']);
    }

    return $object;
  }

  /**
   * 
   * @param int $i
   * @return \BaseX\Resource
   */
  public function get($i)
  {
    if (null === $i || !$this->getResources()->offsetExists($i))
    {
      return null;
    }

    $res = $this->getResources()->offsetGet($i);

    if (!isset($res['object']))
    {
      $res['object'] = $this->getObject($res);
      $this->getResources()->offsetSet($i, $res);
    }

    return $res['object'];
  }

  public function current()
  {
    return $this->get($this->key());
  }

  public function key()
  {
    $this->getResources();
    return $this->getIndex()->key();
  }

  public function next()
  {
    $this->getResources();
    $this->getIndex()->next();
  }

  public function rewind()
  {
    $this->getResources();
    $this->getIndex()->rewind();
  }

  public function valid()
  {
    $this->getResources();
    return $this->getIndex()->valid();
  }

  public function count()
  {
    $this->getResources();
    return $this->getIndex()->count();
  }

  /**
   * 
   * @return \BaseX\Resource
   */
  public function getFirst()
  {
    $this->getResources();
    return $this->get($this->getIndex()->offsetGet(0));
  }

  /**
   * 
   * @return \BaseX\Resource
   */
  public function getLast()
  {
    $this->getResources();
    return $this->get($this->getIndex()->offsetGet($this->getIndex()->count() - 1));
  }

  /**
   * 
   * @return \BaseX\Resource|null
   */
  public function getSingle()
  {
    $this->getResources();
    return $this->getIndex()->count() === 1 ? $this->get($this->getIndex()->offsetGet(0)) : null;
  }

  /**
   * 
   * @param string $key
   * @return \BaseX\Resource\Iterator\ResourceIterator
   */
  protected function sort($key)
  {
    if ($this->sort !== $key)
    {
      $this->sort = $key;
      $this->idx = null;
    }

    return $this;
  }

  /**
   * Rebuilds index.
   */
  protected function reindex()
  {
    $idx = array();

    if (null === $this->sort && count($this->filters) === 0)
    {
      $total = $this->resources->count();
      if (0 === $total)
        $keys = array();
      else
        $keys = range(0, $total - 1);
    }
    else
    {

      $sort = null === $this->sort ? 'path' : $this->sort;

      reset($this->resources);

      foreach ($this->resources as $i => $resource)
      {
        $path = $resource['path'];
        $skip = false;
        foreach ($this->filters as $pattern => $type)
        {
          switch ($type)
          {
            case self::FILTER_REGEX:
              $skip = preg_match($pattern, $path);
              break;
            case self::FILTER_NAME_REGEX:
              $skip = preg_match($pattern, basename($path));
              break;
            case self::FILTER_NAME_GLOB:
              $skip = fnmatch($pattern, basename($path));
              break;
            case self::FILTER_GLOB:
            default:
              $skip = fnmatch($pattern, $path);
              break;
          }

          if ($skip)
            break;
        }

        if ($skip)
          continue;

        $idx[] = $this->resources[$i][$sort];
      }

      asort($idx);

      $keys = array_keys($idx);
    }

    if ($this->reverse)
      $keys = array_reverse($keys);

    $this->idx = new \ArrayIterator($keys);
  }

  /**
   * 
   * @return \ArrayIterator
   */
  protected function getIndex()
  {
    if (null === $this->idx)
    {
      $this->reindex();
    }

    return $this->idx;
  }

  public function filter($pattern, $type = self::FILTER_GLOB)
  {
    if (!isset($this->filters[$pattern]) || $this->filters[$pattern] !== $type)
    {
      $this->filters[$pattern] = $type;
      $this->idx = null;
    }

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
    $this->reverse = true;
    $this->idx = null;
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
    throw new \ErrorException('Not implemented');
  }

  public function offsetUnset($offset)
  {
    throw new \ErrorException('Not implemented.');
  }

}

