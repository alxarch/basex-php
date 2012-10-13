<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Query\Result;

use BaseX\Query;
use BaseX\Query\Result\MapperInterface;

/**
 * Maps wuery results to arrays.
 *
 * @author alxarch
 */
class ArrayMapper implements MapperInterface
{
  public function __construct($delimiter = ' ') {
    $this->delimiter = $delimiter;
  }
  
  public function supportsType($type) {
    return $type === Query::TYPE_ITEM || 
           $type === Query::TYPE_TEXT || 
           $type === Query::TYPE_STRING;
  }

  public function getResult($data, $type) {
    return explode($this->delimiter, $data);
  }
}

