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
use \DOMDocument as XML;
use BaseX\Query\QueryResultsInterface;

/**
 * BaseX Resource for xml documents.
 * 
 * @package BaseX 
 */
class Document extends Streamable
{

  public function isRaw()
  {
    return false;
  }

  /**
   * Returns the contents of the document as XML.
   * 
   * @return \DOMDocument
   */
  public function getXML()
  {
    $xml = new XML();
    $xml->loadXML($this->getContents());
    return $xml;
  }

  /**
   * Retrieves contents of a document filtered by an XPath expression.
   * 
   * @param string $xpath An XPath expression to apply to the contents.
   * @param \BaseX\Query\QueryResultsInterface $results
   * @return string $result
   */
  public function xpath($xpath, QueryResultsInterface $results = null)
  {
    return $this->getDatabase()->xpath($xpath, $this->getPath(), $results);
  }

  public function getWriteMethod()
  {
    return 'replace';
  }

  public function getReadMethod()
  {
    return 'open';
  }

  public function getContents()
  {
    $xql = sprintf("db:open('%s', '%s')", $this->getDatabase(), $this->getPath());
    return $this->getDatabase()->getSession()->query($xql)->execute();
  }

  public function setContents($data)
  {
    $this->getDatabase()->replace($this->getPath(), $data);
  }

}