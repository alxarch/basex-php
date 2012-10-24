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
 * Wrapper for arrays adding extra functionality
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

  /**
   * Constructor.
   * 
   * Initial array can be either 
   * - an ArrayIterator or 
   * - any type convertable to an ArrayIterator
   * 
   * @param mixed $array
   */
  public function __construct($array = null)
  {
    if (null === $array)
      $array = array();
    
    if($array instanceof ArrayIterator)
      $this->iterator = $array;
    else
      $this->iterator = new ArrayIterator($array);
  }

  /**
   * Applies all process instructions to the base array iterator/
   * 
   * @return Traversable
   */
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
   * Returns an iterator according to current process instructions.
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

  /**
   * Gets the first element of the processed array.
   * 
   * @return mixed
   */
  public function getFirst()
  {
    return $this->count() > 0 ? $this->getIterator()->current() : null;
  }

  /**
   * Gets the last element of the processed array.
   * 
   * @return mixed
   */
  public function getLast()
  {
    return $this->count() > 0 ?
      $this->getIterator()->offsetGet($this->count() - 1) : null;
  }

  /**
   * Gets the only element of the processed array.
   * 
   * If the array has more than one elements it returns null
   * 
   * @return mixed
   */
  public function getSingle()
  {
    return $this->count() === 1 ? $this->getIterator()->offsetGet(0) : null;
  }

  /**
   * Sort this array with a callable.
   * 
   * @see usort()
   * @param callable $callback
   * @return \BaseX\Iterator\ArrayWrapper
   */
  public function sort($callback)
  {
    $this->sort = $callback;
    return $this;
  }

  /**
   * Filters this array with a callable.
   * 
   * The callable must accept a single item as parameter and return true/false.
   * 
   * @param callable $callback
   * @return \BaseX\Iterator\ArrayWrapper
   */
  public function filter($callback)
  {
    $this->filters[] = $callback;
    $this->total = null;
    return $this;
  }

  /**
   * Convert each element of the array using a callable.
   * 
   * @param callable $callback
   * @return \BaseX\Iterator\ArrayWrapper
   */
  public function map($callback)
  {
    $this->callback = $callback;
    return $this;
  }

  /**
   * Filter the input array using a regular expression.
   * 
   * @param string $pattern 
   * @return \BaseX\Iterator\ArrayWrapper
   */
  public function grep($pattern)
  {
    $this->grep = $pattern;
    $this->total = null;
    return $this;
  }

  /**
   * Reverse the input array.
   * 
   * @return \BaseX\Iterator\ArrayWrapper
   */
  public function reverse()
  {
    $this->reverse = !$this->reverse;
    return $this;
  }

  /**
   * Count the items in the processed array.
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

  /**
   * Verify offset exists in the processed array.
   * @param int $offset
   * @return boolean
   */
  public function offsetExists($offset)
  {
    return is_int($offset)
      && $offset > 0
      && $this->count() > 0
      && $offset < $this->count();
  }
  /**
   * Fet a single element from the processed array at a specified offset.
   * 
   * @param int $offset
   * @return mixed
   */
  public function offsetGet($offset)
  {
    return $this->offsetExists($offset) && $this->getIterator()->offsetGet($offset);
  }

  /**
   * Not implemented. ArrayWrapper is read only.
   * 
   * @param mixed $offset
   * @param mixed $value
   * @throws Exception
   */
  public function offsetSet($offset, $value)
  {
    throw new Exception('Not implemented.');
  }

  /**
   * Not implemented. ArrayWrapper is read only.
   * 
   * @param mixed $offset
   * @throws Exception
   */
  public function offsetUnset($offset)
  {
    throw new Exception('Not implemented.');
  }

}
