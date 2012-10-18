<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Resource;

use BaseX\Query;
use BaseX\Query\Results\ProcessedResults;
use BaseX\Database;
use BaseX\Resource\Raw;
use BaseX\Resource\Document;
use BaseX\Resource;

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
    return true;
//    return $type === Query::TYPE_ELEMENT;
  }
  
  public function supportsMethod($method) 
  {
    return true;
//    return 'xml' === $method;
  }
  
  protected function processData($data, $type)
  {
    $xml = @simplexml_load_string($data);
    
    if(false !== $xml 
        && $xml->getName() === 'resource' 
        && isset($xml['content-type']) 
        && isset($xml['modified-date'])
        && isset($xml['raw'])
            )
    {
    
      $path = (string) $xml;
      $mime = (string) $xml['content-type'];
      $modified = (string)$xml['modified-date'];
      $raw = 'true' === (string) $xml['raw'];

      if($raw)
      {
        $size = (int) $xml['size'];
        $resource = new Raw($this->db, $path);
        $resource->setSize($size);
      }
      else
      {
        $resource = new Document($this->db, $path);
      }
      
      $resource->setModified(\DateTime::createFromFormat(Resource::DATE_FORMAT, $modified));
      $resource->setContentType($mime);

      return $resource;
    }
    
    return null;
  }
}

