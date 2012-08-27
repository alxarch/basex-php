<?php

namespace BaseX;

use BaseX\Resource;
use \DOMDocument as XML;

/**
 * BaseX\Resource for non-raw resources. 
 */
class Document extends Resource
{
  /**
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
  public function __construct(Database $db, $path)
  {
    parent::__construct($db, $path);
    if($this->isRaw())
      throw new \InvalidArgumentException('Specified resource is stored as raw file.');
  }
  
  /**
   * Retrieves the contents of a file.
   * 
   * In case of an xml file it uses the dom and flushes it as a string.
   * 
   * @return type 
   */
  public function getContents()
  {
    if($this->isRaw())
    {
      return $this->doGetContents();
    }
    else
    {
      // This way any changes made to the DOM will be visible.
      return $this->getXML()->saveXML();
    }
  }
  
  /**
   * Sets the contents of a document.
   * 
   * Storing any changes to the database must occur in a separate step.
   * 
   * Any local changes made to the xml tree will be lost.
   * 
   * @param type $contents
   * @return \BaseX\Document
   */
  public function setContents($contents)
  {
    $this->xml = null;
    return parent::setContents($contents);
  }
  
  /**
   * Returns the contents of the document as XML.
   * 
   * @return \DOMDocument
   */
  public function getXML()
  {
    if($this->isRaw())
    {
      return null;
    }

    // Avoid cyclic calls by not using getContents directly.
    if(null === $this->xml)
    {
      $this->xml = XML::loadXML($this->doGetContents());
    }
    
    return $this->xml;
  }
  

  /**
   * Retrieves contents of a document filtered by an XPath expression.
   * 
   * @param string $xpath An XPath expression to apply to the contents.
   * @return string $result
   */
  public function xpath($xpath)
  {
    return $this->getDatabase()->xpath($xpath, $this->getPath());
  }
}