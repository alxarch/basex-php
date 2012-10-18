<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Resource;

use BaseX\Query\Results\ProcessedResults;
use BaseX\Database;
use BaseX\Resource\Raw;
use BaseX\Resource\Document;

/**
 * Description of ResourceResult
 *
 * @author alxarch
 */
class ResourceResults extends ProcessedResults
{
  
  /**
   * @var \BaseX\Database;
   */
  protected $db;
  

  public function __construct(Database $db)
  {
    $this->db = $db;
    
  }
  
  public function supportsType($type)
  {
    return $type === Query::TYPE_ELEMENT;
  }
  
  public function supportsMethod($method) 
  {
    return 'xml' === $method;
  }
  
  protected function processData($data, $type)
  {
    $xml = @simplexml_load_string($data);
    
    if($xml instanceof \SimpleXMLElement && 'resource' === $xml->getName())
    {
      if('false' === (string)$xml['raw'])
      {
        return Document::fromSimpleXML($this->db, $xml);
      }
      else
      {
        return Raw::fromSimpleXML($this->db, $xml);
      }
    }
    
    return null;
  }
}

