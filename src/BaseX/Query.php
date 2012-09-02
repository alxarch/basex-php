<?php

namespace BaseX;

use BaseX\Exception;
use BaseX\Session;

/*
 * PHP client for BaseX.
 * Works with BaseX 7.0 and later
 *
 * Documentation: http://docs.basex.org/wiki/Clients
 * 
 * (C) BaseX Team 2005-12, BSD License
 */
class Query
{
  
  const INIT = 0;
  const CLOSE = 2;
  const BIND= 3;
  const RESULTS = 4;
  const EXECUTE = 5;
  const INFO = 6;
  const OPTIONS = 7;
  const CONTEXT = 14;
  const UPDATING = 30;
  const FULL = 31;

  /**
   * 
   *
   * @var BaseX\Session
   */
  protected $session;
  
  /**
   *
   * @var integer
   */
  protected $id;
  
  /**
   *  @var boolean
   */
  protected $open;
  
  /**
   *
   * @param BaseX\Session $session The session to use
   * @param string $xquery The query to execute
   * 
   */
  public function __construct(Session $session, $xquery)
  {
    $this->session = $session;
    $this->id = (int) $this->session->sendQueryCommand(self::INIT, $xquery, true);
  }
  
  /**
   * Binds an external variable to a value.
   * 
   * @param string $name   The name of the variable (without '$')
   * @param mixed  $value  The value to assign to the variable
   * @param string $type   A type to cast the value to
   * 
   * @return BaseX\Query $this
   */
  public function bind($name, $value, $type = "")
  {
    $this->session->sendQueryCommand(self::BIND, array($this->id, $name, $value, $type), true);
    
    return $this;
  }

  /**
   * Sets context for the query.
   * 
   * @param string $value
   * @param string $type 
   * 
   * @return BaseX\Query $this
   */
  public function context($value, $type = "") 
  {
    $this->session->sendQueryCommand(self::CONTEXT, array($this->id, $value, $type), true);
    
    return $this;
  }

  /**
   * Executes the query.
   * 
   * @return string|int Results of the query. If session redirects it returns 
   * size of bytes writen to output.
   */
  public function execute()
  {
    return $this->session->sendQueryCommand(self::EXECUTE, $this->id);
  }
  
  /**
   * Gets query info
   * @return string
   */
  public function info() 
  {
    return $this->session->sendQueryCommand(self::INFO, $this->id, true);
  }
  
  /**
   * Gets query options
   * @return string
   */
  public function options()
  {
    return $this->session->sendQueryCommand(self::OPTIONS, $this->id, true);
  }
   
  /**
   * Gets the session for this query.
   * 
   * @return BaseX\Session
   */
  public function getSession()
  {
    return $this->session;
  }
  
  /**
   * Gets this query id.
   * 
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
 
}