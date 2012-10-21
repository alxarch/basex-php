<?php

namespace BaseX\Resource\Iterator;

class Wrapper extends \ArrayIterator
{
  protected $converter;
  
  /**
   *
   * @var \ArrayIterator
   */
  protected $array;
  
  public function __construct(\Traversable $array, $converter=null)
  {
    parent::__construct(iterator_to_array($array));
    
    if(null !== $converter && !is_callable($converter))
      throw new \InvalidArgumentException('Invalid Converter');
    
    $this->converter = $converter;
  }
  
  public function getFirst()
  {
    return $this->count() > 0 ? $this->offsetGet(0) : 0;
  }
  
  public function getLast()
  {
    $total = $this->count();
    return $total > 0 ? $this->offsetGet($total - 1) : null;
  }
  
  public function getSingle()
  {
    return $this->count() === 1 ? $this->offsetGet(0) : null;
  }
  
  protected function convert($resource)
  {
    return call_user_func($this->converter, $resource);
  }
  
  public function offsetGet($index)
  {
    $item = parent::offsetGet($index);
    if(null === $item) return $item;
    return $this->convert($item);
  }

  public function current()
  {
    $item = parent::current();
    if(null === $item) return $item;
    return $this->convert($item);
  }
}