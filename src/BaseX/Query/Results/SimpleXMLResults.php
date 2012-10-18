<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Query\Results;

use BaseX\Query\Results\ProcessedResults;
use BaseX\Query;

/**
 * Description of SimpleXMLQueryResults
 *
 * @author alxarch
 */
class SimpleXMLResults extends ProcessedResults
{
  protected function processData($data, $type)
  {
    $xml = @simplexml_load_string($data);
    return false === $xml ? null : $xml;
  }

  public function supportsType($type) {
    return $type === Query::TYPE_NODE ||
           $type === Query::TYPE_DOCUMENT || 
           $type === Query::TYPE_ELEMENT || 
           $type === Query::TYPE_DOCUMENT_ELEMENT
            ;
  }

  public function supportsMethod($method)
  {
    return 'xml' === $method;
  }
}
