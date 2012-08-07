<?php

namespace BaseX;

use BaseX\Session;

/*
 * PHP client for BaseX.
 * Works with BaseX 7.0 and later
 *
 * Documentation: http://docs.basex.org/wiki/Clients
 * 
 * (C) BaseX Team 2005-12, BSD License
 */
class Query {
  /**
   * 
   *
   * @var BaseX\Client\Session
   */
  protected $session;
  
  /**
   *
   * @var string
   */
  protected $id;
  
  /**
   *  @var boolean
   */
  protected $open;
  
  /**
   *
   * @param \BaseX\Session $session The session to use
   * @param string $xquery The query to execute
   * 
   */
  public function __construct(Session $session, $xquery) {
    $this->session = $session;
    $this->id = $this->exec(chr(0), $xquery);
  }
  
  /**
   * Binds an external variable to a value.
   * 
   * @param string $name   The name of the variable (without '$')
   * @param mixed  $value  The value to assign to the variable
   * @param string $type   A type to cast the value to
   * 
   * @return \BaseX\Query $this
   */
  public function bind($name, $value, $type = "") {
    $this->exec(chr(3), Session::concat($this->id, $name, $value, $type));
    return $this;
  }

  /**
   * Sets context for the query.
   * 
   * @param string $value
   * @param string $type 
   * 
   * @return \BaseX\Query $this
   */
  public function context($value, $type = "") {
    $this->exec(chr(14), Session::concat($this->id, $value, $type));
    return $this;
  }

  /**
   * Executes the query.
   * 
   * @return mixed The result of the query
   */
  public function execute() {
    return $this->exec(chr(5), $this->id);
  }
  
  /* see readme.txt */
  public function info() {
    return $this->exec(chr(6), $this->id);
  }
  
  /* see readme.txt */
  public function options() {
    return $this->exec(chr(7), $this->id);
  }
  
  /* see readme.txt */
  protected function exec($cmd, $arg)
  {
    $this->session->send($cmd.$arg);
    $s = $this->session->receive();
    
    if($this->session->ok() != True) 
    {
      throw new Exception($this->session->readString());
    }
    return $s;
  }
  
  /**
   * Gets the session for this query.
   * 
   * @return \BaseX\Client\Session
   */
  public function getSession(){
    return $this->session;
  }
  
  /**
   * Sets the session for this query.
   * 
   * @return \BaseX\Client\Query $this
   */
  public function setSession(Session $session){
    $this->session = $session;
    return $this;
  }
  
  /**
   * Gets this query id.
   * 
   * @return string
   */
  public function getId(){
    return $this->id;
  }
 
 
}