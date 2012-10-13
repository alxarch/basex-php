<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Resource\Streamable;
use BaseX\Query\Result\MapperInterface;
use \DOMDocument as XML;


/**
 * BaseX Resource for xml documents.
 * 
 * @package BaseX 
 */
class Document extends Streamable
{
  public function isRaw() {
    return false;
  }
  
  public function getSize() {
    return null;
  }

  /**
   * Returns the contents of the document as XML.
   * 
   * @return \DOMDocument
   */
  public function getXML()
  {
    $xml = new XML();
    $xml->loadXML($this->read());
    return $xml;
  }

  /**
   * Retrieves contents of a document filtered by an XPath expression.
   * 
   * @param string $xpath An XPath expression to apply to the contents.
   * @param \BaseX\Query\QueryResultFactoryInterface $factory
   * @return string $result
   */
  public function xpath($xpath, MapperInterface $mapper=null)
  {
    return $this->getDatabase()->xpath($xpath, $this->getPath(), $mapper);
  }
  
  public function creationMethod() {
    return 'replace';
  }
}