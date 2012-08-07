<?php

namespace BaseX\Client;

use BaseX\Client\Query;

/** 
 * @file PHP client for BaseX.
 * Works with BaseX 7.0 and later
 *
 * Documentation: http://docs.basex.org/wiki/Clients
 * 
 * (C) BaseX Team 2005-12, BSD License
 */

/**
 * Session communicating with BaseX Server.
 *
 * Socket-based implementation.
 *
 */ 
class Session {
  /**
   *
   * @var resource
   */
  protected $socket;
  
  /**
   * @var string
   */
   protected $info = null;
   
  /**
   * @var string
   */
   protected $buffer = null;
   
   /**
    *
    * @var int
    */
   protected $bpos = null;
   
   /**
    *
    * @var int
    */
   protected $bsize = null;


  function __construct($h, $p, $user, $pw) {
    // create server connection
    $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if(!socket_connect($this->socket, $h, $p)) {
      throw new \Exception("Can't communicate with server.");
    }

    // receive timestamp
    $ts = $this->readString();

    // send username and hashed password/timestamp
    $md5 = hash("md5", hash("md5", $pw).$ts);
    socket_write($this->socket, self::concat($user, $md5, ''));

    // receives success flag
    if(socket_read($this->socket, 1) != chr(0)) {
      throw new \Exception("Access denied.");
    }
  }

  /* see readme.txt */
  public function execute($com) {
    // send command to server
    socket_write($this->socket, $com.chr(0));

    // receive result
    $result = $this->receive();
    $this->info = $this->readString();
    if(!$this->ok()) {
      throw new \Exception($this->info);
    }
    return $result;
  }
  
  /* Returns the query object.*/
  public function query($q) {
    return new Query($this, $q);
  }
  
  /* see readme.txt */
  public function create($path, $input) {
    $this->sendCmd(8, $path, $input);
  }
  
  /* see readme.txt */
  public function add($path, $input) {
    $this->sendCmd(9, $path, $input);
  }

  /* see readme.txt */
  public function replace($path, $input) {
    $this->sendCmd(12, $path, $input);
  }

  /* see readme.txt */
  public function store($path, $input){
    $this->sendCmd(13, $path, $input);
  }
  
  /* see readme.txt */
  public function getInfo(){
    return $this->info;
  }

  /* see readme.txt */
  public function close() {
    socket_write($this->socket, "exit".chr(0));
    socket_close($this->socket);
  }

  /* Initializes the byte transfer */
  private function init() {
    $this->bpos = 0;
    $this->bsize = 0;
  }

  /**
   * Receives a string from the socket. 
   * 
   * @return string
   */
  protected function readString() {
    $com = "";
    while(($d = $this->read()) != chr(0)) {
      $com .= $d;
    }
    return $com;
  }

  /**
   * Returns a single byte from the socket. 
   * 
   * @return char
   */
  protected function read() {
    if($this->bpos === $this->bsize) {
      $this->bsize = socket_recv($this->socket, $this->buffer, 4096, 0);
      $this->bpos = 0;
    }
    return $this->buffer[$this->bpos++];
  }
  
  /* see readme.txt */
  protected function sendCmd($code, $arg, $input) {
    $parts = array(chr($code), $arg, chr(0), $input, $chr(0));
    socket_write($this->socket, implode('', $parts));
    $this->info = $this->receive();
    if($this->ok() != True) {
      throw new \Exception($this->info);
    }
  }
  
  /* Sends the str. */
  protected function send($str) {
    socket_write($this->socket, $str.chr(0));
  }
  
  /* Returns success check. */
  protected function ok() {
    return $this->read() == chr(0);
  }
  
  /* Returns the result. */
  protected function receive() {
    $this->init();
    return $this->readString();
  }
  
  static public function concat(){
    $parts = func_get_args();
    return implode(chr(0), $parts);
  }
}
