<?php

namespace BaseX;

use BaseX\Query;
use BaseX\Session\Socket;
use BaseX\Helpers as B;
use BaseX\Session\Exception;

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
  const GAP = 0;
  const OK = 0;
  const QUERY = 0;
  const CREATE = 8;
  const ADD = 9;
  const REPLACE = 12;
  const STORE = 13;
  
  /**
   *
   * @var BaseX\SocketClient
   */
  protected $socket;
  
  /**
   *
   * @var string
   */
  protected $version;
  
  /**
   * @var string
   */
   protected $info = null;

   /**
    * Creates a new Session
    * 
    * @param string $host Server hostname
    * @param string $port Port to use
    * @param string $user Username
    * @param type $pass   Password
    * @throws \Exception 
    */
  function __construct($host, $port, $user, $pass) 
  {
    $this->socket = new Socket($host, $port);
    
    $this->authenticate($user, $pass);
    
//    $this->info = $this->execute('INFO');
    
//    $this->version = preg_replace('/Version:\s+(\d+\.\d+)/', '$1', $this->info);
  }
  
  protected function authenticate($user, $pass)
  {
    // receive timestamp
    $ts = $this->socket->read(true);
    
    // send username and hashed password/timestamp
    $msg = $user . chr(0) . hash("md5", hash("md5", $pass) . $ts) . chr(0);
    
    $this->socket->send($msg);
    
    // receives success flag
    if(!$this->ok()) 
    {
      throw new Exception("Access denied.");
    }
  }

  /**
   * Executes a database command.
   * 
   * @param string $com The command to execute
   * @return mixed $result The command output
   */
  public function execute($command) 
  {
    $this->socket->send($command.chr(0));
    $result = $this->socket->read(true);
    $this->info = $this->socket->read();
    if(!$this->ok()) 
    {
      throw new Exception($this->info);
    }
    
    return $result;
  }
  
  public function script($script)
  {
//    $this->requireVersion('7.4');
   
    $command = sprintf('EXECUTE "%s"', $script);
    return $this->execute($command);
  }
  
  /**
   * Creates a new Query that uses this session.
   * 
   * @param string $q XQuery code
   * @return BaseX\Query $q
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
  public function create($name, $input = '') 
  {
    $this->sendCmd(self::CREATE, $name, $input);
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
    $this->sendCmd(self::ADD, $path, $input);
  }

  /**
   * Replaces a document at the specified path.
   * 
   * @param string $path Path to overwrite
   * @param mixed $input Document contents
   */
  public function replace($path, $input)
  {
    $this->sendCmd(self::REPLACE, $path, $input);
  }

  /**
   * Stores a raw file in the opened database to the specified [path].
   * 
   * @see http://docs.basex.org/wiki/Commands#STORE
   * 
   * @param string $path
   * @param string $input 
   */
  public function store($path, $input)
  {
    $this->sendCmd(self::STORE, $path, $input);
  }
  
  /**
   * Closes the connection to the server 
   */
  public function close()
  {
    $this->socket->send("exit".chr(0));
    $this->socket->close();
  }

  private function sendCmd($code, $arg, $input) 
  {
    $msg = chr($code).$arg.chr(0).$input.chr(0);
    
    $this->socket->send($msg);

    $this->info = $this->socket->read(true);
    
    if(!$this->ok())
      throw new Exception($this->info);
  }
  
  
  private function ok()
  {
    return $this->socket->readSingle() === chr(0);
  }

  public function send($code, $arg)
  {
    if(is_array($arg))
      $arg = implode (chr(0), $arg);
    
    $this->socket->send(chr($code).$arg.chr(0));
    
    $result = $this->socket->read(true);
    
    if(!$this->ok())
    {
      throw new Exception($this->socket->read());
    }
    
    return $result;
  }
  
//  public function getVersion()
//  {
//    return $this->version;
//  }
  
//  protected function requireVersion($ver)
//  {
//    if($this->version < $ver)
//    {
//      throw new Exception('Not available in this version.');
//    }
//  }
}
