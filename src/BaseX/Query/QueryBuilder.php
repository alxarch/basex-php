<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Query;

use BaseX\Helpers as B;
use BaseX\Query;
use BaseX\Query\QueryBuilder;
use BaseX\Session;
use InvalidArgumentException;

/**
 * Helper class to facilitate xquery writting. 
 * 
 * @package BaseX
 */
class QueryBuilder
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
  
  /**
   * 
   * @return array
   */
  public function getParameters()
  {
    return $this->parameters;
  }
  
  /**
   * 
   * @param string $name
   * @return string
   */
  public function getParameter($name)
  {
    return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
  }
  
  /**
   * 
   * @param string $name
   * @param string $value
   * @return BaseX\QueryBuilder
   */
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
  
  /**
   * 
   * @return array
   */
  public function getVariables()
  {
    return $this->variables;
  }
  
  /**
   * 
   * @param string $name
   * @return mixed
   */
  public function getVariable($name)
  {
    return isset($this->variables[$name]) ? $this->variables[$name] : null;
  }
  
  /**
   * 
   * @param string $name
   * @param mixed $defaultValue
   * @return QueryBuilder
   */
  public function addExternalVariable($name, $defaultValue=null)
  {
    $this->variables[$name] = $defaultValue;
    return $this;
  }
  
  /**
   * Add external variable definitions to the query.
   * 
   * @param array $variables
   * @return \BaseX\QueryBuilder
   */
  public function addExternalVariables($variables)
  {
    foreach($variables as $key => $value)
    {
      if(!is_string($key))
      {
        $key = (string)$value;
        $value = null;
      }
      
      if($key != '')
        $this->addExternalVariable($key, $value);
    }
    
    return $this;
  }
  
  /**
   * 
   * @param string $name
   * @return string
   */
  public function getOption($name)
  {
    return isset($this->options[$name]) ? $this->options[$name] : null;
  }
  
  /**
   * 
   * @param string $name
   * @param string $value
   * @return QueryBuilder
   */
  public function setOption($name, $value)
  {
    if(is_string($name))
      $this->options[$name] = $value;
    return $this;
  }
  
  /**
   * 
   * @param array $options
   * @return QueryBuilder
   */
  public function setOptions($options)
  {
    foreach($options as $key => $value)
    {
      
      $this->setOption($key, $value);
    }
    return $this;
  }
  
  /**
   * 
   * @return array
   */
  public function getOptions()
  {
    return $this->options;
  }
  
  /**
   * 
   * @param string $body
   * @return QueryBuilder
   */
  public function setBody($body)
  {
    $this->body = $body;
    
    return $this;
  }
  
  /**
   * 
   * @return string
   */
  public function getBody()
  {
    return $this->body;
  }
  
  /**
   * 
   * @return string
   */
  public function build()
  {
    $xq = array();
    
    
    
    foreach ($this->getNamespaces() as $alias => $uri)
    {
      $xq[] = sprintf("declare namespace %s = '%s';", $alias, $uri);
    }
    
    foreach ($this->getModules() as $alias => $uri)
    {
      $xq[] = sprintf("import module namespace %s = '%s';", $alias, $uri);
    }
    
    foreach ($this->getParameters() as $name => $value)
    {
      $xq[] = sprintf("declare option output:%s %s;", $name, B::value($value));
    }
    
    foreach ($this->getOptions() as $name => $value)
    {
      $xq[] = sprintf("declare option db:%s %s;", $name, B::value($value));
    }
    
    foreach ($this->getVariables() as $name => $value)
    {
      if(null === $value)
      {
        $xq[] = sprintf("declare variable $%s external;", $name);
      }
      else
      {
        $xq[] = sprintf("declare variable $%s external := %s;", $name, B::value($value));
      }
    }
    
    $xq[] = $this->getBody();
    
    return implode("\n", $xq);
  }
  
  /**
   * 
   * @return array
   */
  public function getModules()
  {
    return $this->modules;
  }
  
  /**
   * 
   * @param array $modules
   * @return QueryBuilder
   */
  public function setModules($modules)  
  {
    foreach ($modules as $m => $uri)
    {
      $this->setModule($m, $uri);
    }
    
    return $this;
  }
  
  /**
   * 
   * @param string $alias
   * @param string $uri
   * @return QueryBuilder
   * @throws InvalidArgumentException if alias is invalid
   */
  public function setModule($alias, $uri)
  {
    if(!preg_match('/^[a-zA-Z0-9\-_.]+$/', $alias))
      throw new InvalidArgumentException('Invalid module alias: '.$alias);
    
    $this->modules[$alias] = $uri;
    return $this;
  }
  
  /**
   * 
   * @return array
   */
  public function getNamespaces()
  {
    return $this->namespaces;
  }
  
  /**
   * 
   * @param array $namespaces
   * @return QueryBuilder
   */
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
    if(!preg_match('/^[a-zA-Z0-9\-_.]+$/', $alias))
      throw new InvalidArgumentException('Invalid namespace alias: '.$alias);
    $this->namespaces[$alias] = $uri;
    return $this;
  }
  
  /**
   *
   * @param Session $session
   * @return Query
   */
  public function getQuery(Session $session)
  {
    
    $q = $session->query($this->build());
    
    return $q;
  }
  
  /**
   * 
   * @return QueryBuilder
   */
  public static function begin()
  {
    $class = get_called_class();
    return new $class();
  }
  
}