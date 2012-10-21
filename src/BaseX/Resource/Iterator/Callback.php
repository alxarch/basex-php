<?php

namespace BaseX\Resource\Iterator;

class Callback extends \IteratorIterator
{
  protected $callback;
  
  /**
   *
   * @var \ArrayIterator
   */
  protected $array;
  
  public function __construct(\Traversable $iter, $callback=null)
  {
    parent::__construct($iter);
    
    if(null !== $callback && !is_callable($callback))
      throw new \InvalidArgumentException('Invalid Converter');
    
    $this->callback = $callback;
  }
  
  public function current()
  {
    $item = parent::current();
    
    if(null === $item) 
      return $item;
    
    return call_user_func($this->callback, $item);
  }
}