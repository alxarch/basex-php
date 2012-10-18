<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Query\Results;

use BaseX\Query\Results\ProcessedResults;

/**
 * Description of SerializableResults
 *
 * @author alxarch
 */
class UnserializableResults extends ProcessedResults
{
  /**
   *
   * @var \ReflectionClass
   */
  protected $class;
  
  /**
   *
   * @var array
   */
  protected $args;
  
  protected $method;
  
  public function __construct($class, $args=array()) 
  {
    $class = new \ReflectionClass($class);
    
    if(!$this->isValidClass($class, $args))
    {
      throw new \InvalidArgumentException('Invalid class provided.');
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
  }
  
  protected function getUnserializer(\ReflectionClass $class)
  {
    if($class->hasMethod('unserialize'))
    {
      return 'unserialize';
    }
    
    foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $meth)
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
    if(!$class instanceof \ReflectionClass)
    {
      $class = new \ReflectionClass($class);
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

  protected function processData($data, $type) 
  {
    $class = $this->class->newInstanceArgs($this->args);
    $class->{$this->method}($data);
    return $class;
  }
}
