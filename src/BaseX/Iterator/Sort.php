<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Iterator;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Sorts an input iterator using a callable.
 */
class Sort implements IteratorAggregate
{
  protected $callback;
  protected $iterator;
  
  public function __construct(Traversable $iterator, $callback=null)
  {
    $this->callback = $callback;
    $this->iterator = $iterator;
  }

  /**
   * 
   * @return \ArrayIterator
   */
  public function getIterator()
  {
    $data = iterator_to_array($this->iterator);
    
    if(null === $this->callback)
    {
      asort($data);
    }
    else
    {
      uasort($data, $this->callback);
    }
    
    return new ArrayIterator($data);
  }
  
  
}