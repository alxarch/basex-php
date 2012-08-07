<?php

namespace BaseX;

use BaseX\Query;

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
class Session 
{
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

   /**
    * Creates a new Session
    * 
    * @param string $host Server hostname
    * @param string $port Port to use
    * @param string $user Username
    * @param type $pass   Password
    * @throws \Exception 
    */
  function __construct($host, $port, $user, $pass) {
    // create server connection
    $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    
    if(!socket_connect($this->socket, $host, $pass)) 
    {
      throw new \Exception("Can't communicate with server.");
    }

    // receive timestamp
    $ts = $this->readString();

    // send username and hashed password/timestamp
    $md5 = hash("md5", hash("md5", $pass).$ts);
    
    socket_write($this->socket, self::concat($user, $md5, ''));

    // receives success flag
    if(socket_read($this->socket, 1) != chr(0)) 
    {
      throw new \Exception("Access denied.");
    }
  }

  /**
   * Executes a database command.
   * 
   * @param string $com The command to execute
   * @return mixed $result The command output
   * @throws \Exception If there was any errors on command execution
   */
  public function execute($com) {
    // send command to server
    socket_write($this->socket, $com.chr(0));

    // receive result
    $result = $this->receive();
    
    $this->info = $this->readString();
    
    if(!$this->ok()) 
    {
      throw new \Exception($this->info);
    }
    
    return $result;
  }
  
  /**
   * Creates a new Query that uses this session.
   * 
   * @param string $q XQuery code
   * @return \BaseX\Query $q
   */
  public function query($q) 
  {
    return new Query($this, $q);
  }
  
  /**
   * Creates the database [name] with an optional [input] and opens it.
   * 
   * @see http://docs.basex.org/wiki/Commands#CREATE_DB
   * 
   * @param type $name name of the new database
   * @param type $input initial document
   */
  public function create($name, $input) 
  {
    $this->sendCmd(8, $name, $input);
  }
  
  /**
   * Adds the files, directory or XML string specified by [input] 
   * to the currently opened database at the specified [path].
   * 
   * @see http://docs.basex.org/wiki/Commands#ADD
   * 
   * @param string $path path to add
   * @param mixed $input document contents
   *
   */
  public function add($path, $input)
  {
    $this->sendCmd(9, $path, $input);
  }

  /**
   * Replaces a document at the specified path.
   * 
   * @param string $path Path to overwrite
   * @param mixed $input Document contents
   */
  public function replace($path, $input)
  {
    $this->sendCmd(12, $path, $input);
  }

  /**
   * Stores a raw file in the opened database to the specified [path].
   * 
   * @see http://docs.basex.org/wiki/Commands#STORE
   * 
   * @param string $path
   * @param string $input 
   */
  public function store($path, $input='')
  {
    $this->sendCmd(13, $path, $input);
  }
  
  /**
   * Retrieves information about the session.
   * 
   * @return string
   */
  public function getInfo()
  {
    return $this->info;
  }

  /**
   * Closes the connection to the server 
   */
  public function close()
  {
    socket_write($this->socket, "exit".chr(0));
    socket_close($this->socket);
  }

  /* Initializes the byte transfer */
  private function init()
  {
    $this->bpos = 0;
    $this->bsize = 0;
  }

  /**
   * Receives a string from the socket. 
   * 
   * @return string
   */
  protected function readString()
  {
    $com = "";
    while(($d = $this->read()) != chr(0)) 
    {
      $com .= $d;
    }
    return $com;
  }

  /**
   * Returns a single byte from the socket. 
   * 
   * @return char
   */
  protected function read()
  {
    if($this->bpos === $this->bsize) 
    {
      $this->bsize = socket_recv($this->socket, $this->buffer, 4096, 0);
      $this->bpos = 0;
    }
    
    return $this->buffer[$this->bpos++];
  }
  
  /* see readme.txt */
  protected function sendCmd($code, $arg, $input)
  {
    $parts = array(chr($code), $arg, chr(0), $input, $chr(0));
    
    socket_write($this->socket, implode('', $parts));
    
    $this->info = $this->receive();
    
    if(!$this->ok()) 
    {
      throw new \Exception($this->info);
    }
  }
  
  /* Sends the str. */
  protected function send($str)
  {
    socket_write($this->socket, $str.chr(0));
  }
  
  /* Returns success check. */
  protected function ok()
  {
    return $this->read() == chr(0);
  }
  
  /* Returns the result. */
  protected function receive()
  {
    $this->init();
    return $this->readString();
  }
  
  /**
   * Helper function to concatenate server messages
   * 
   * @param mixed $args All arguments will be concateneted
   * @return string
   */
  static private function concat()
  {
    $parts = func_get_args();
    return implode(chr(0), $parts);
  }
}
