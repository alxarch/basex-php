<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Query;

use BaseX\Query\QueryResult;
use \SimpleXMLElement;
use \ArrayAccess;
use BaseX\Helpers as B;

/**
 * Query result wrapper for xml results.
 * 
 * Uses SimpleXMLElement to 'magically' access values.
 * 
 * @author alxarch
 */
class SimpleXMLResult extends QueryResult implements ArrayAccess
{
  /**
   *
   * @var \SimpleXMLElement
   */
  protected $data;

  /**
   * 
   * @param int $type
   * @return boolean
   */
  static public function getSupportedTypes() 
  {
    return range(8, 16);
  }

  /**
   * 
   * @param mixed $data
   * @return \BaseX\Query\SimpleXMLResult
   * @throws \InvalidArgumentException
   */
  public function setData($data) 
  {
    if(is_string($data))
    {
      $data = @simplexml_load_string($data);
    }
    
    if($data instanceof SimpleXMLElement)
    {
      $this->data = $data;
    }
    else
    {
      throw new \InvalidArgumentException('Invalid data provided.');
    }
    
    return $this;
  }
  
  /**
   * 
   * @return string
   */
  public function getData() 
  {
    return substr($this->data->asXML(), strlen('<?xml version="1.0"?>'));
  }
  
  /**
   * 
   * @return \SimpleXMLElement
   */
  public function getXML()
  {
    return $this->data;
  }
  
  /**
   * 
   * @param string $path
   * @return array an array of SimpleXMLElement objects or <b>FALSE</b> in
	 * case of an error.
   */
  public function xpath($path)
  {
    return $this->data->xpath($path);
  }
  
  public function offsetSet($offset, $value) {
    throw new \Exception('Not implemented.');
  }

  public function offsetExists($offset) {
    return isset($this->data[$offset]);
  }
  
  public function offsetUnset($offset) {
    throw new \Exception('Not implemented.');
  }

  public function offsetGet($offset) 
  {
    $method = 'get'.  B::camelize($offset);
    
    if(method_exists($this, $method))
    {
      return $this->{$method}();
    }
    
    return isset($this->data[$offset]) ? (string) $this->data[$offset] : null;
  }
  
  public function __isset($name)
  {
    return isset($this->data->{$name});
  }
  
  public function __get($name) 
  {
    $method = 'get'.  B::camelize($name);
    if(method_exists($this, $method))
    {
      return $this->{$method}();
    }
    
    if(!isset($this->data->{$name}))
    {
      return null;
    }
    
    $value = $this->data->{$name};
    
    if(count($value) > 1)
    {
      return $value;
    }
    
    $children = $value->children();
    if(empty($children))
    {
      return B::convert((string) $value);
    }
    else
    {
      return $value;
    }
    
    
  }
}

