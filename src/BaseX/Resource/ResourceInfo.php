<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Resource;

use BaseX\Session;
use BaseX\Query\SimpleXMLResult;
use BaseX\Query\QueryBuilder;

/**
 * Description of ResourceInfo
 *
 * @author alxarch
 */
class ResourceInfo extends SimpleXMLResult
{
  public function setData($data) 
  {
    parent::setData($data);
    
    if($this->data->getName() === 'resource' &&
       isset($this->data['modified-date']) && 
       isset($this->data['content-type']) && 
       isset($this->data['raw']))
    {
      return $this;
    }
    
    throw new \InvalidArgumentException('Invalid resource data provided.');
  
  }
  
  public function getSize()
  {
    return isset($this->data['size']) ? (int) $this->data['size'] : 0;
  }
  
  public function getModifiedDate()
  {
    return (string)$this->data['modified-date'];
  }
  
  public function getContentType() {
    return (string)$this->data['content-type'];
  }
  
  public function isRaw()
  {
    return 'true' === (string)$this->data['raw'];
  }
  
  public function getPath()
  {
    return (string)  $this->data;
  }
  
  static public function get(Session $session, $db, $path=null)
  {
    return QueryBuilder::begin()
            ->setBody("db:list-details('$db', '$path')")
            ->getQuery($session)
            ->getResults(get_called_class());
  }
}

