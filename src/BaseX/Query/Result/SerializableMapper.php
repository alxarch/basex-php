<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Query\Result;

use BaseX\Error\ResultMapperError;
use BaseX\Query\Result\MapperInterface;
use Serializable;

/**
 * Maps a Serializable object to a result.
 *
 * @author alxarch
 */
class SerializableMapper implements MapperInterface
{
  
  protected $class;
  
  public function __construct($class) 
  {
    if(is_object($class) && $class instanceof Serializable)
    {
      $this->class = get_class($class);
    }
    elseif(is_string($class) && 
           in_array('Serializable',  class_implements($class)))
    {
        $this->class = $class;
    }
    
    if(null === $this->class)
    {
      throw new \InvalidArgumentException('Invalid class provided.');
    }
  }
  
  public function supportsType($type){
    return true;
  }

  public function getResult($data, $type) {
    $class = $this->class;
    try
    {
      $object = new $class();
      $object->unserialize($data);
      return $object;
    }
    catch (Exception $e)
    {
      throw new ResultMapperError($e->getMessage());
    }
  }
}
