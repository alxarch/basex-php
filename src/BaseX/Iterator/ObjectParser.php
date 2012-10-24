<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Iterator;

use IteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use InvalidArgumentException;
use Traversable;


/**
 * Description of Unserializable
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
  
  public function __construct(Traversable $traversable, $class, $args=array()) 
  {
    
    $class = new ReflectionClass($class);
    
    if(!$this->isValidClass($class, $args))
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
        if(isset($args[$name]))
        {
          $this->args[$pos] = $args[$name];
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
