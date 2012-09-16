<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Query;

use BaseX\Session;
use BaseX\Query\QueryBuilder;

/**
 * Description of DataProvider
 *
 * @author alxarch
 */
abstract class QueryResultProvider 
{
  static protected $instances = array();
  
  protected $query;
  protected $defaults;
  
  /**
   * @return string
   */
  abstract public function getResultClass();
  
  /**
   * @return QueryBuilder
   */
  abstract public function getQueryBuilder();

  protected function __construct(Session $session) 
  {
    $builder = $this->getQueryBuilder();
    if($builder instanceof QueryBuilder)
    {
      $this->query = $builder->getQuery($session);
      $this->defaults = $builder->getVariables();
    }
    else
    {
      $msg = sprintf('Query builder instance must be returned by %s::getQueryBuilder()', get_called_class());
      throw new Error($msg);
    }
  }
  
  public function getQuery()
  {
    return $this->query;
  }
  
  public function getDefaults()
  {
    return $this->defaults;
  }
  
  public function getResults($params=array())
  {
    $query = $this->getQuery();
    $defaults = $this->getDefaults();
    
    foreach ($defaults as $name => $value)
    {
      if(isset($params[$name]))
      {
        $value = $params[$value];
      }
      
      $query->bind($name, $value);
    }
    
    $results = $query->getResults($this->getResultClass());
    
    if(empty($results))
    {
      return null;
    }
    
    if(count($results) > 1)
    {
      return $results;
    }
    
    return $results[0];
  }
  
  static public function getInstance(Session $session)
  {
    foreach (self::$instances as $instance)
    {
      if($instance->getQuery()->getSession() === $session)
      {
        return $instance;
      }
    }
    
    $class = get_called_class();
    
    $instance = new $class($session);
    
    self::$instances[] = $instance;
    
    return $instance;
  }
  
  static public function get(Session $session, $params=array())
  {
    return self::getInstance($session)->getResults($params);
  }
}

