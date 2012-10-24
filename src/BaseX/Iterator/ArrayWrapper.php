<?php

/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Iterator;

use ArrayAccess;
use ArrayIterator;
use BaseX\Iterator\CallbackFilter;
use BaseX\Iterator\CallbackParser;
use BaseX\Iterator\GrepFilter;
use BaseX\Iterator\Reverse;
use BaseX\Iterator\Sort;
use Countable;
use Exception;
use IteratorAggregate;
use Traversable;

/**
 * Description of ArrayWrapper
 *
 * @author alxarch
 */
abstract class ArrayWrapper implements IteratorAggregate, ArrayAccess, Countable
{

  /**
   *
   * @var ArrayIterator
   */
  protected $iterator;
  protected $reverse = false;
  protected $callback;
  protected $filter;
  protected $grep;
  protected $sort;
  protected $total;

  public function __construct($array = null)
  {
    if (null === $array)
      $array = array();

    $this->iterator = new ArrayIterator($array);
  }

  protected function processIterator()
  {
    $iterator = $this->iterator;
    
    if (null !== $this->grep)
    {
      $iterator = new GrepFilter($iterator, $this->grep);
    }

    if (null !== $this->filter)
    {
      $iterator = new CallbackFilter($iterator, $this->filter);
    }

    if (null !== $this->callback)
    {
      $iterator = new CallbackParser($iterator, $this->callback);
    }

    if (null !== $this->sort)
    {
      $iterator = new Sort($iterator, $this->sort);
    }

    if (true === $this->reverse)
    {
      $iterator = new Reverse($iterator);
    }

    return $iterator;
  }

  /**
   * 
   * Returns an iterator according to current settings.
   * 
   * Order of application is:
   * 
   * - grep
   * - filter
   * - map
   * - sort
   * - reverse
   * 
   * @return ArrayIterator
   */
  public function getIterator()
  {
    $result = $this->processIterator();

    if (!($result instanceof ArrayIterator))
    {
      $array = iterator_to_array($result);
      $result = new ArrayIterator($array);
    }
    
    return $result;
  }

  public function getFirst()
  {
    return $this->count() > 0 ? $this->getIterator()->current() : null;
  }

  public function getLast()
  {
    return $this->count() > 0 ?
      $this->getIterator()->offsetGet($this->count() - 1) : null;
  }

  public function getSingle()
  {
    return $this->count() === 1 ? $this->getIterator()->offsetGet(0) : null;
  }

  public function sort($callback)
  {
    $this->sort = $callback;
    return $this;
  }

  public function filter($callback)
  {
    $this->filters[] = $callback;
    $this->total = null;
    return $this;
  }

  public function map($callback)
  {
    $this->callback = $callback;
    return $this;
  }

  public function grep($pattern)
  {
    $this->grep = $pattern;
    $this->total = null;
    return $this;
  }

  public function reverse()
  {
    $this->reverse = !$this->reverse;
    return $this;
  }

  /**
   * 
   * @return int
   */
  public function count()
  {
    if (null === $this->total)
    {
      $this->total = $this->getIterator()->count();
    }

    return $this->total;
  }

  public function offsetExists($offset)
  {
    return is_int($offset)
      && $offset > 0
      && $this->count() > 0
      && $offset < $this->count();
  }

  public function offsetGet($offset)
  {
    return $this->offsetExists($offset) && $this->getIterator()->offsetGet($offset);
  }

  public function offsetSet($offset, $value)
  {
    throw new Exception('Not implemented.');
  }

  public function offsetUnset($offset)
  {
    throw new Exception('Not implemented.');
  }

}
