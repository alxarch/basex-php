<?php

namespace BaseX;

use BaseX\Query;
use BaseX\Session\Socket;
use BaseX\Helpers as B;
use BaseX\Session\Exception;
use BaseX\Session\Info as SessionInfo;

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
  const OK = "\x00";
  const CREATE = 8;
  const ADD = 9;
  const REPLACE = 12;
  const STORE = 13;
  
  /**
   *
   * @var BaseX\Session\Socket
   */
  protected $socket;
  
  /**
   *
   * @var string
   */
  protected $version;
  
  /**
   * @var \BaseX\Session\Info
   */
   protected $info = null;
   
  /**
   * @var string
   */
   protected $status = null;

   /**
    * 
    * @var resource
    */
   protected $out = null;
   
   /**
    * Locks the curent session.
    * @var boolean
    */
   protected $locked = false;

   
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
  }
  
  /**
   *
   * @return \BaseX\Session\Socket
   */
  public function getSocket()
  {
    return $this->socket;
  }
  
  /**
   *
   * @param resource $to
   * @return \BaseX\Session $this 
   */
  public function redirectOutput($to)
  {
    $this->out = $to;
    return $this;
  }
  
  /**
   *
   * @return resource 
   */
  public function redirectsTo()
  {
    return $this->out;
  }
  
  public function getInfo()
  {
    if(null === $this->info)
    {
      $this->info = new SessionInfo($this);
    }
    
    return $this->info;
  }
  
  public function getStatus()
  {
    return (string)$this->status;
  }
  
  protected function authenticate($user, $pass)
  {
    // receive timestamp
    $this->socket->clearBuffer();
    $ts = $this->socket->read();
    
    // send username and hashed password/timestamp
    $hash = hash("md5", hash("md5", $pass) . $ts);
    
    $msg = implode(array($user , Socket::NUL, $hash, Socket::NUL));
    
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
   * @return string|int 
   */
  public function execute($command) 
  {
    $this->checkLock();
    
    $this->socket->send($command . Socket::NUL );
    
    $this->socket->clearBuffer();
    
    if(is_resource($this->out))
    {
      $result = $this->socket->readInto($this->out);
    }
    else 
    {
      $result = $this->socket->read();
    }
    
    $this->status = $this->socket->read();
    
    if(!$this->ok())
    {
      throw new Exception($this->getStatus());
    }
    
    return $result;
  }
  
  public function script($script)
  {
//    $this->requireVersion('7.4');
    return $this->execute("EXECUTE \"$script\"");
  }
  
  /**
   * Creates a new Query that uses this session.
   * 
   * @param string $q XQuery code
   * @return \BaseX\Query $q
   */
  public function query($q) 
  {
//    $this->checkLock();
    return new Query($this, $q);
  }
  
  /**
   * Creates the database [name] with an optional [input] and opens it.
   * 
   * @see http://docs.basex.org/wiki/Commands#CREATE_DB
   * 
   * @param string $name name of the new database
   * @param string|resource $input initial document
   */
  public function create($name, $input = '') 
  {
    $this->sendCommand(self::CREATE, $name, $input);
  }
  
  /**
   * Adds the files, directory or XML string specified by [input] 
   * to the currently opened database at the specified [path].
   * 
   * @see http://docs.basex.org/wiki/Commands#ADD
   * 
   * @param string $path path to add
   * @param string|resource $input document contents
   *
   */
  public function add($path, $input)
  {
    $this->sendCommand(self::ADD, $path, $input);
  }

  /**
   * Replaces a document at the specified path.
   * 
   * @param string $path Path to overwrite
   * @param string|resource $input Document contents
   */
  public function replace($path, $input)
  {
    $this->sendCommand(self::REPLACE, $path, $input);
  }

  /**
   * Stores a raw file in the opened database to the specified [path].
   * 
   * @see http://docs.basex.org/wiki/Commands#STORE
   * 
   * @param string $path
   * @param string|resource $input 
   */
  public function store($path, $input)
  {
    $this->sendCommand(self::STORE, $path, $input);
  }
  
  /**
   * Closes the connection to the server 
   */
  public function close()
  {
    $this->checkLock();
    $this->socket->send("EXIT".Socket::NUL);
    $this->socket->close();
  }

  private function sendCommand($code, $arg, $input) 
  {
    $this->checkLock();
 
    if(is_resource($input))
    {
      //  In case input is a resource allow the socket to pipe it in.
      $msg = sprintf("%c%s%s", $code, $arg, Socket::NUL);
      $this->socket->send($msg);
      $this->socket->send($input);
      $this->socket->send(Socket::NUL);
    }
    else
    {
      $msg = sprintf("%c%s%s%s%s", $code, $arg, Socket::NUL, B::scrub($input), Socket::NUL);
      $this->socket->send($msg);
    }
 
    $this->socket->clearBuffer();
    $this->status = $this->socket->read();
    
    if(!$this->ok())
      throw new Exception($this->getStatus());
  }
  
  
  private function ok()
  {
    return $this->socket->readSingle() === self::OK;
  }

  /**
   *
   * @param int $code
   * @param string $arg
   * @param boolean $noredirects Don't redirect output.
   * @return mixed
   * @throws Exception 
   */
  public function sendQueryCommand($code, $arg, $noredirects = false)
  {
    $this->checkLock();
    if(is_array($arg))
      $arg = implode (Socket::NUL, $arg);

    $msg = sprintf("%c%s%s", $code, $arg, Socket::NUL);
    $this->socket->send($msg);
    $this->socket->clearBuffer();
    
    if($noredirects || !is_resource($this->out))
    {
      $result = $this->socket->read();
    }
    else 
    {
      $result = $this->socket->readInto($this->out);
    }
    if(!$this->ok())
    {
      throw new Exception($this->socket->read());
    }
    
    $this->socket->clearBuffer();
    return $result;
  }
  
  protected function checkLock()
  {
    if($this->isLocked())
    {
      throw new Exception("Session is locked.");
    }
  }
  
  
  public function isLocked()
  {
    return $this->locked;
  }
  
  public function lock()
  {
    $this->locked = true;
  }
  
  public function unlock()
  {
    $this->locked = false;
  }
  
  public function getOption($name)
  {
    return $this->getInfo()->option($name);
  }
  
  public function setOption($name, $value)
  {
    if($value instanceof SessionInfo)
      $value = $value->option($name);
    
    $this->execute("SET $name \"$value\"");
    return $this;
  }
  
  public function resetOption($name)
  {
    $this->execute("SET $name");
    return $this;
  }
  
//  protected function requireVersion($ver)
//  {
//    if($this->version < $ver)
//    {
//      throw new Exception('Not available in this version.');
//    }
//  }
}
