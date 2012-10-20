<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */


namespace BaseX\Query\Results;

use BaseX\Query\Results\ProcessedResults;

/**
 * Parse query results with a callable
 *
 * @author alxarch
 */
class CallbackResults extends ProcessedResults
{
  /**
   *
   * @var \Closure
   */
  public $callback;

  public function __construct($callback)
  {
    if(!is_callable($callback))
      throw new \InvalidArgumentException('Non callable callback.');
    $this->callback = $callback;
  }
  
  protected function processData($data, $type) 
  {
    return call_user_func($this->callback, $data, $type);
  }

  public function supportsMethod($method) 
  {
    return  true;
  }
}
