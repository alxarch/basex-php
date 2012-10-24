<?php

/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */


namespace BaseX\Query;

use Countable;
use IteratorAggregate;


/**
 *
 * @author alxarch
 */
interface QueryResultsInterface extends IteratorAggregate, Countable
{
  public function addResult($data, $type);
  public function supportsType($type);
  public function supportsMethod($method);
}
