<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Query;

use BaseX\Query\QueryResultInterface;
use BaseX\Session;

/**
 * Wrapper class for Query Results
 *
 * @author alxarch
 */
class QueryResult implements QueryResultInterface
{
  protected $type;
  protected $data;
  protected $session;
  
  public function getSession() {
    return $this->session;
  }
  public function setSession(Session $session = null) {
    $this->session = $session;
    return $this;
  }

  public static function getSupportedTypes() 
  {
    return array_merge(range(7, 15), range(32, 83));
  }

  public function getType()
  {
    return $this->type;
  }
  
  public function setType($type)
  {
    if(in_array($type, self::getSupportedTypes()))
    {
      $this->type = $type;
      return $this;
    }
    
    throw new \InvalidArgumentException('Unsupported result type.');
  }
  
  public function getData()
  {
    return $this->data;
  }
  
  public function setData($data)
  {
    $this->data = $data;
    return $this;
  }
}
