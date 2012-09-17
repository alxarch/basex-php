<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Database;
use BaseX\Resource;
use \DOMDocument as XML;
use BaseX\Error;

/**
 * BaseX Resource for xml documents.
 * 
 * @package BaseX 
 */
class Document extends Resource
{
  /**
   * The contents of the resource as a DOMDocument
   * 
   * @var \DOMDocument
   */
  protected $xml = null;
  
  /**
   * Constructor
   * 
   * @param Database $db
   * @param string $path 
   */
  protected function init()
  {
    if($this->isRaw())
      throw new Error('Specified resource is not a document.');
  }

  /**
   * Returns the contents of the document as XML.
   * 
   * @return \DOMDocument
   */
  public function getXML()
  {
    return XML::loadXML($this->getContents());
  }
  
  /**
   * Returns the contents of the document as XML.
   * 
   * @return \DOMDocument
   */
  public function setXML(\DOMDocument $xml)
  {
    return $this->setContents($xml->saveXML());
  }

  /**
   * Retrieves contents of a document filtered by an XPath expression.
   * 
   * @param string $xpath An XPath expression to apply to the contents.
   * @return string $result
   */
  public function xpath($xpath)
  {
    $db = new Database($this->getSession(), $this->getDatabase());
    return $db->xpath($xpath, $this->getPath());
  }
}