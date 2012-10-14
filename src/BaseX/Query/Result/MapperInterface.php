<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Query\Result;

/**
 *
 * @author alxarch
 */
interface MapperInterface {


  public function getResult($data, $type);
  public function supportsType($type);
}
