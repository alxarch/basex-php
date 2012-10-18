<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
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
}
