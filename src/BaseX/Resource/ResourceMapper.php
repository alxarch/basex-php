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
use BaseX\Query\Result\SimpleXMLMapper;
use BaseX\Query\Result\SimpleXMLMapperInterface;
use BaseX\Error\ResultMapperError;
use BaseX\Database;
use BaseX\Resource\Raw;
use BaseX\Resource\Document;
use BaseX\Resource\Collection;
use \SimpleXMLElement;

/**
 * Description of ResourceResult
 *
 * @author alxarch
 */
class ResourceMapper extends SimpleXMLMapper implements SimpleXMLMapperInterface
{
  
  /**
   * @var BaseX\Session;
   */
  protected $db;
  
  public function __construct(Database $db) {
    $this->db = $db;
  }
  
  public function supportsType($type)
  {
    return $type === Query::TYPE_NODE || 
           $type === Query::TYPE_ELEMENT;
  }
  
  /**
   * 
   * @param SimpleXMLElement $xml
   * @return \BaseX\Resource\Document|\BaseX\Resource\Collection|null
   */
  public function getResultFromXML(SimpleXMLElement $xml)
  {
    $name = $xml->getName();
    
    if($name === 'resource')
    {
      $path = (string) $xml;
      $modified = (string) $xml['modified-date'];
      $mime = (string) $xml['content-type'];
      $raw = 'true' === (string) $xml['raw'];
      
      if($raw)
      {
        $size = (int) $xml['size'];
        
        $resource = new Raw($this->db, $path, $modified);
        $resource->setSize($size);
      }
      else
      {
        $resource = new Document($this->db, $path, $modified);
      }
      
      $resource->setContentType($mime);
      
      return $resource;
    }
    elseif($name === 'collection')
    {
      $path = (string) $xml['path'];
      $modified = (string) $xml['modified-date'];
      
      return new Collection($this->db, $path, $modified);
    }
    
    return null;
    
  }

  public function getResult($data, $type) 
  {
    $xml = parent::getResult($data, $type);
    
    $resource = $this->getResultFromXML($xml);
    if(null === $resource)
    {
      throw new ResultMapperError();
    }
    return $resource;

  }
}

