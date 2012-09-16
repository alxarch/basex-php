<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Resource;

use BaseX\Query\SimpleXMLResult;

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
    return 'true' === $this->data['raw'];
  }
  
  public function getPath()
  {
    return (string)  $this->data;
  }
  
  
}

