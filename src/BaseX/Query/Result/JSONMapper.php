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
use BaseX\Error\ResultMapperError;
use BaseX\Query\Result\MapperInterface;

/**
 * Description of QueryResultFactory
 *
 * @author alxarch
 */
class JSONMapper implements MapperInterface
{
  public function __construct($assoc = true) {
    $this->assoc = $assoc;
  }
  
  public function supportsType($type) {
    return $type === Query::TYPE_ITEM ||
           $type === Query::TYPE_TEXT || 
           $type === Query::TYPE_STRING;
  }

  public function getResult($data, $type) {
    $json = @json_decode($data, $this->assoc);
    if(null === $json)
    {
      throw new ResultMapperError();
    }
    return $json;
  }
}
