<?php


namespace BaseX\Resource\Iterator;

class Reverse implements \IteratorAggregate
{

  protected $iterator;
  
  public function __construct(\Traversable $iterator)
  {
    $this->iterator = $iterator;
  }

  public function getIterator()
  {
    return new \ArrayIterator(reverse(iterator_to_array($this->iterator)));
  }
  
  
}