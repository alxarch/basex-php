<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Query\Results;

use BaseX\Query\QueryResults;

/**
 * Description of ProcessedQueryReults
 *
 * @author alxarch
 */
abstract class ProcessedResults extends QueryResults
{
  
  protected $cache = array();
  
  public function offsetGet($offset) {
    if(isset($this->cache[$offset]))
    {
      return $this->cache[$offset];
    }
    
    $data = parent::offsetGet($offset);

    $result = $this->processData($data, $this->types[$offset]);
    
    $this->cache[$offset] = $result;
    return $result;
  }
  
  public function current() {
    return $this->offsetGet($this->current);
  }

  abstract protected function processData($data, $type);
  
  public function asArray()
  {
    if(count($this->cache) < count($this->data))
    {
      foreach ($this->data as $i => $d)
      {
        $this->cache[$i] = $this->processData($data, $this->types[$i]);
      }
    }
    
    return $this->cache;
  }
}
