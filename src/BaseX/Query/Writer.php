<?php

namespace BaseX\Query;

use BaseX\Session;

/**
 * Helper class to facilitate xquery writting. 
 */
class Writer
{
  /**
   * Options to declare in the prologue.
   * 
   * @var array
   */
  protected $options = array();
  
  /**
   * Output parameters to set in the prologue.
   * 
   * @var array
   */
  protected $parameters = array();
  
  /**
   * Variables to bind in the query.
   * 
   * @var array
   */
  protected $variables = array();
  
  /**
   * Namespaces to declare in the query prologue.
   * 
   * @var array
   */
  protected $namespaces = array();
  
  /**
   * Modules to import in the query prologue.
   * 
   * @var array
   */
  protected $modules = array();
  
  /**
   * Query body
   * @var string
   */
  protected $body;
  
  public function __construct($xquery, $variables=array())
  {
    $this->setBody($xquery);
    $this->setVariables($variables);
  }
  
  public function getParameters()
  {
    return $this->parameters;
  }
  
  public function getParameter($name)
  {
    return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
  }
  
  public function setParameter($name, $value)
  {
    $this->parameters[$name] = $value;
    return $this;
  }
  
  public function setParameters($parameters)
  {
    foreach($parameters as $key => $value)
    {
      $this->setParameter($key, $value);
    }
    return $this;
  }
  
  
  public function getVariables()
  {
    return $this->variables;
  }
  
  public function getVariable($name)
  {
    return isset($this->variables[$name]) ? $this->variables[$name] : null;
  }
  
  public function setVariable($name, $value)
  {
    $this->variables[$name] = $value;
    return $this;
  }
  
  public function setVariables($variables)
  {
    foreach($variables as $key => $value)
    {
      $this->setVariable($key, $value);
    }
    return $this;
  }
  
  public function getOption($name)
  {
    return isset($this->options[$name]) ? $this->options[$name] : null;
  }
  
  public function setOption($name, $value)
  {
    $this->options[$name] = $value;
    return $this;
  }
  
  public function setOptions($options)
  {
    foreach($options as $key => $value)
    {
      $this->setOption($key, $value);
    }
    return $this;
  }
  
  public function getOptions()
  {
    return $this->options;
  }
  
  public function setBody($body)
  {
    $this->body = $body;
    
    return $this;
  }
  
  public function getBody()
  {
    return $this->body;
  }
  
  public function build()
  {
    $xq = array();
    
    foreach ($this->variables as $name => $value)
    {
      $xq[] = sprintf('declare variable $%s external;', $name);
    }
    
    foreach ($this->namespaces as $alias => $uri)
    {
      $xq[] = sprintf('declare namespace %s = "%s";', $alias, $uri);
    }
    
    foreach ($this->modules as $alias => $uri)
    {
      $xq[] = sprintf('import module namespace %s = "%s";', $alias, $uri);
    }
    
    foreach ($this->parameters as $name => $value)
    {
      $xq[] = sprintf('declare option output:%s = "%s";', $name, $value);
    }
    
    foreach ($this->options as $name => $value)
    {
      $xq[] = sprintf('declare option db:%s = "%s";', $name, $value);
    }
    
    
    $xq[] = $this->getBody();
    
    return implode("\n", $xq);
  }
  
  public function getModules()
  {
    return $this->modules;
  }
  
  public function setModules($modules)  
  {
    foreach ($modules as $m => $uri)
    {
      $this->setModule($m, $uri);
    }
    
    return $this;
  }
  
  public function setModule($alias, $uri)
  {
    $this->modules[$alias] = $uri;
    return $this;
  }
  
  public function getNamespaces()
  {
    return $this->namespaces;
  }
  
  public function setNamespaces($namespaces)  
  {
    foreach ($namespaces as $n => $uri)
    {
      $this->setNamespace($n, $uri);
    }
    
    return $this;
  }
  
  public function setNamespace($alias, $uri)
  {
    $this->namespaces[$alias] = $uri;
    return $this;
  }
  
  public function getQuery(Session $session)
  {
    
    $q = $session->query($this->build());
    
    foreach ($this->variables as $name => $value)
    {
      $q->bind($name, $value);
    }
    
    return $q;
  }
}