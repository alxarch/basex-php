<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Query\Result;

use BaseX\Error\ResultMapperError;
use BaseX\Query\Result\MapperInterface;
use BaseX\Query;

/**
 * Query results mapper for xml data that returns a SimpleXMLElement instance.
 *
 * @author alxarch
 */
class SimpleXMLMapper implements MapperInterface
{
  
  /**
   * 
   * @param int $type
   * @return boolean
   */
  public function supportsType($type) {
    return $type === Query::TYPE_NODE ||
           $type === MapperInterface::TYPE_SIMPLEXML ||
           $type === Query::TYPE_DOCUMENT || 
           $type === Query::TYPE_ELEMENT || 
           $type === Query::TYPE_DOCUMENT_ELEMENT
            ;
  }
  
  /**
   * 
   * @param string $data
   * @param int $type
   * @return SimpleXMLElement
   * @throws ResultMapperError
   */
  public function getResult($data, $type) {
    $xml = $data instanceof \SimpleXMLElement ? $data : @simplexml_load_string($data);
    if(false === $xml)
    {
      throw new ResultMapperError();
    }
    return $xml;
  }
}

