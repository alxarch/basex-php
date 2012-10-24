<?php


namespace BaseX\Iterator;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Reverse implements IteratorAggregate
{

  protected $iterator;
  
  public function __construct(Traversable $iterator)
  {
    $this->iterator = $iterator;
  }

  public function getIterator()
  {
    $array = iterator_to_array($this->iterator);
    $rev = array_reverse($array);
    return new ArrayIterator($rev);
  }

}