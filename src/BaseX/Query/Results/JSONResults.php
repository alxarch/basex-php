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
 * QueryResults for json data
 *
 * @author alxarch
 */
class JSONResults extends ProcessedResults
{
  public $assoc;
  public $depth;

  public function __construct($associative=false, $depth=512)
  {
    $this->assoc = (int)$associative;
    $this->depth = (int)$depth;
  }
  
  protected function processData($data, $type) {
    return json_decode($data, $this->assoc, $this->depth);
  }

  public function supportsMethod($method) 
  {
    return 'json' === $method || 'jsonml' === $method || 'text' === $method;
  }
}
