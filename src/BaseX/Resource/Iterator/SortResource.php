<?php

namespace BaseX\Resource\Iterator;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

class SortResource implements IteratorAggregate
{
  protected $key;
  protected $iterator;
  
  public function __construct(Traversable $iterator, $key)
  {
    if(!in_array($key, array('size', 'path', 'modified', 'mime')))
      throw new InvalidArgumentException('Invalid sort key.');
    
    $this->key = $key;
    $this->iterator = $iterator;
  }

  public function getIterator()
  {
    
    $data = iterator_to_array($this->iterator);
    
    $key = $this->key;
    uasort($data, function($a, $b) use ($key){
      if($a[$key] === $b[$key])
      {
        return 0;
      }
      
      return $a[$key] > $b[$key] ? -1 : 1;
    });
    
    return new ArrayIterator($data);
  }
  
  
}