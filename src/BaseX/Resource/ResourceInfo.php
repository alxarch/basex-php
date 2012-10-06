<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Session;
use BaseX\Query\SimpleXMLResult;
use BaseX\Query\QueryBuilder;

/**
 * Query Result wrapper for Resource Info
 *
 * @author alxarch
 */
class ResourceInfo extends SimpleXMLResult
{
  public function setData($data) 
  {
    parent::setData($data);
    
    if($this->xml->getName() === 'resource' &&
       isset($this->xml['modified-date']) && 
       isset($this->xml['content-type']) && 
       isset($this->xml['raw']))
    {
      return $this;
    }
    
    throw new \InvalidArgumentException('Invalid resource data provided.');
  
  }
  
  public function getSize()
  {
    return isset($this->xml['size']) ? (int) $this->xml['size'] : 0;
  }
  
  public function getModifiedDate()
  {
    return (string)$this->xml['modified-date'];
  }
  
  public function getContentType() {
    return (string)$this->xml['content-type'];
  }
  
  public function isRaw()
  {
    return 'true' === (string)$this->xml['raw'];
  }
  
  public function getPath()
  {
    return (string)  $this->xml;
  }
  
  static public function get(Session $session, $db, $path=null)
  {
    return QueryBuilder::begin()
            ->setBody("db:list-details('$db', '$path')")
            ->getQuery($session)
            ->getResults(get_called_class());
  }
}

