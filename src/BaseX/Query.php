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
  
  public function __construct(Session $s, $q) {
    $this->session = $s;
    $this->id = $this->exec(chr(0), $q);
  }
  
  /* see readme.txt */
  public function bind($name, $value, $type = "") {
    $this->exec(chr(3), Session::concat($this->id, $name, $value, $type));
  }

  /* see readme.txt */
  public function context($value, $type = "") {
    $this->exec(chr(14), Session::concat($this->id, $value, $type));
  }

  /* see readme.txt */
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
  public function close() {
    $this->exec(chr(2), $this->id);   
  }
  
  /* see readme.txt */
  protected function exec($cmd, $arg)
  {
    $this->session->send($cmd.$arg);
    $s = $this->session->receive();
    if($this->session->ok() != True) {
      throw new Exception($this->session->readString());
    }
    return $s;
  }
  
  /**
   *
   * @return BaseX\Client\Session
   */
  public function getSession(){
    return $this->session;
  }
  
   /**
   *
   * @return BaseX\Client\Query
   */
  public function setSession(Session $session){
    $this->session = $session;
    return $this;
  }
  
  public function getId(){
    return $this->id;
  }
 
 
}