<?php

namespace BaseX\Resource\Iterator;

class Converter extends \IteratorIterator
{
  protected $converter;
  
  /**
   *
   * @var \ArrayIterator
   */
  protected $array;
  
  public function __construct(\Traversable $iter, $converter=null)
  {
    parent::__construct($iter);
    
    if(null !== $converter && !is_callable($converter))
      throw new \InvalidArgumentException('Invalid Converter');
    
    $this->converter = $converter;
  }
  
  public function current()
  {
    $item = parent::current();
    if(null === $item) 
      return $item;
    return call_user_func($this->converter, $item);
  }
}