<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Iterator;

use IteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use InvalidArgumentException;
use Traversable;


/**
 * Parses items in the input iterator by unserializing them to objects.
 * 
 * Looks for a method named 'unserialize' or 
 * a method with the annotation '@unserialize' in the given class
 * 
 * @author alxarch
 */
class ObjectParser extends IteratorIterator
{
  /**
   *
   * @var ReflectionClass
   */
  protected $class;
  
  /**
   *
   * @var array
   */
  protected $args;
  
  protected $method;
  
  /**
   * Parses items in the input iterator by unserializing them to objects.
   * 
   * Looks for a method named 'unserialize' or a method with the annotation 
   * '@unserialize' in the given class.
   * 
   * @param Traversable $traversable
   * @param string|object $class The class to use for new objects.
   * @param array $params Extra parameters for the constructor.
   * @throws InvalidArgumentException If no unserialization method can be found
   * or if the given parameters are not sufficient to instanciate the objects.
   */
  public function __construct(Traversable $traversable, $class, $params=array()) 
  {
    
    $class = new ReflectionClass($class);
    
    if(!$this->isValidClass($class, $params))
    {
      throw new InvalidArgumentException('Invalid class provided.');
    }
    
    $this->class = $class;
    
    $this->args = array();
    
    $constructor = $this->class->getConstructor();
    if(null !== $constructor)
    {
      foreach ($constructor->getParameters() as $p)
      {
        $name = $p->getName();
        $pos = $p->getPosition();
        if(isset($params[$name]))
        {
          $this->args[$pos] = $params[$name];
        }
      }
    }
    
    $this->method = $this->getUnserializer($class);
    
     parent::__construct($traversable);
  }
  
  protected function getUnserializer(ReflectionClass $class)
  {
    if($class->hasMethod('unserialize'))
    {
      return 'unserialize';
    }
    
    foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $meth)
    {
      $doc = $meth->getDocComment();
      
      if(false === $doc || false === strpos($doc, '@unserialize'))
      {
        continue;
      }
      
      return $meth->getName();
    
    }
    
    return null;
  }


  public function isValidClass($class, $args=array())
  {
    if(!$class instanceof ReflectionClass)
    {
      $class = new ReflectionClass($class);
    }
    
    if(null === $this->getUnserializer($class))
    {
      return false;
    }
    
    $contructor = $class->getConstructor();
    if(null === $contructor)
      return true;
    
    $params = $contructor->getParameters();
    
    if(count($params) === 0)
    {
      return true;
    }
    
    
    foreach ($params as $p)
    {
      if($p->isOptional() || isset($args[$p->getName()]))
      {
        continue;
      }
      
      return false;
    }
    
    return true;
    
  }

  public function current()
  {
    $data = parent::current();
    
    $instance = $this->class->newInstanceArgs($this->args);
    $instance->{$this->method}($data);
    
    return $instance;
  }
}
