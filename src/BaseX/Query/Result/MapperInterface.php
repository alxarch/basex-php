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
  
  // Constants to hint support for mapping from php data.
  
  const TYPE_SIMPLEXML = 1000;
  const TYPE_PHPARRAY = 1001;
  const TYPE_DOMXML = 1002;
  const TYPE_PHPOBJECT = 1003;

  public function getResult($data, $type);
  public function supportsType($type);
}
