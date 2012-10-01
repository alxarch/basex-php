<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Query;

/**
 * Interface for query result wrappers.
 * 
 * @author alxarch
 */
interface QueryResultInterface 
{
  public function getData();
  public function setData($data);
  public function getType();
  public function setType($type);
  
  public static function getSupportedTypes();
}
