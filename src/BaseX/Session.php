<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2005-12, BaseX Team
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * 
 * @author BaseX Team
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * 
 * @license BSD License
 */

namespace BaseX;

use BaseX\Query;
use BaseX\Query\QueryBuilder;
use BaseX\Session\Socket;
use BaseX\Helpers as B;
use BaseX\Error\SessionError;
use BaseX\Session\SessionInfo;

/** 
 * Session for communicating with a BaseX server.
 * 
 * @package BaseX
 */ 
class Session 
{
  const OK = "\x00";
  const CREATE = 8;
  const ADD = 9;
  const REPLACE = 12;
  const STORE = 13;
  
  /**
   * Socket wrapper
   * 
   * @var \BaseX\Session\Socket
   */
  protected $socket;
  
  /**
   * Session information & options
   * 
   * @var \BaseX\Session\Info
   */
   protected $info = null;
   
  /**
   * Last operation's status message.
   * 
   * @var string
   */
   protected $status = null;

   /**
    * Output redirection
    * 
    * @var resource
    */
   protected $out = null;
   
   /**
    * Locks the curent session.
    * 
    * @var boolean
    */
   protected $locked = false;

   protected $providers = array();

   /**
    * Creates a new Session
    * 
    * @param string $host Server hostname
    * @param string $port Port to use
    * @param string $user Username
    * @param string $pass Password
    * 
    * @throws \BaseX\Error\SessionError
    */
  function __construct($host, $port, $user, $pass) 
  {
    $this->socket = new Socket($host, $port);
    
    $this->authenticate($user, $pass);
  }
  
  /**
   * Gets the socket wrapper.
   *
   * @return \BaseX\Session\Socket
   */
  public function getSocket()
  {
    return $this->socket;
  }
  
  /**
   * Redirect content output to a stream.
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
   * Resource at wich session's output is redirected
   * 
   * @return resource 
   */
  public function redirectsTo()
  {
    return $this->out;
  }
  
  /**
   * Gets session information & options wrapper.
   * 
   * @return \BaseX\Session\SessionInfo
   */
  public function getInfo()
  {
    if(null === $this->info)
    {
      $results = QueryBuilder::begin()
//                  ->setParameter('omit-xml-declaration', false)
                  ->setBody('db:system()')
                  ->getQuery($this)
                  ->getResults('BaseX\Session\SessionInfo');
      
      if(null === $results)
      {
        throw new SessionError('Could not load session info.');
      }
      $this->info = $results[0];
    }
    
    return $this->info;
  }
  
  /**
   * Gets last operation's status message.
   * 
   * @return string
   */
  public function getStatus()
  {
    return (string)$this->status;
  }
  
  /**
   * Authenticate a newly opened session according to BaseX Server Protocol
   *  
   * @param string $user
   * @param string  $pass
   * 
   * @throws \BaseX\Error\SessionError On failure
   */
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
      throw new SessionError("Access denied.");
    }
  }

  /**
   * Executes a database command.
   * 
   * @param string $com The command to execute
   * 
   * @return mixed
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
      throw new SessionError($this->getStatus());
    }
    
    return $result;
  }
  
  /**
   * Executes a command script.
   * 
   * Requires BaseX version >= 7.4 
   * 
   * @param string $script
   * @return mixed 
   */
  public function script($script)
  {
//    $this->requireVersion('7.4');
    return $this->execute("EXECUTE \"$script\"");
  }
  
  /**
   * Creates a new Query that uses this session.
   * 
   * @param string $q XQuery code
   * @return BaseX\Query $q
   */
  public function query($q) 
  {
//    $this->checkLock();
    return new Query($this, $q);
  }
  
  /**
   * Creates a database.
   * 
   * @link http://docs.basex.org/wiki/Commands#CREATE_DB
   * 
   * @param string $name name of the new database
   * @param string|resource $input initial document
   */
  public function create($name, $input = '') 
  {
    $this->sendCommand(self::CREATE, $name, $input);
  }
  
  /**
   * Adds documents to the the currently opened database.
   * 
   * @link http://docs.basex.org/wiki/Commands#ADD
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
   * Stores a raw file in the opened database.
   * 
   * @link http://docs.basex.org/wiki/Commands#STORE
   * 
   * @param string $path
   * @param string|resource $input 
   */
  public function store($path, $input)
  {
    $this->sendCommand(self::STORE, $path, $input);
  }
  
  /**
   * Closes the connection to the server.
   * 
   */
  public function close()
  {
    $this->checkLock();
    $this->socket->send("EXIT".Socket::NUL);
    $this->socket->close();
  }
  
  /**
   * Send a command and receive answer using Command Protocol
   *
   * @link http://docs.basex.org/wiki/Server_Protocol#Command_Protocol
   * 
   * @param int $code
   * @param string $arg
   * @param string|resource $input
   * 
   * @throws \BaseX\Error\SessionError
   */
  public function sendCommand($code, $arg, $input) 
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
      throw new SessionError($this->getStatus());
  }
  
  
  private function ok()
  {
    return $this->socket->readSingle() === self::OK;
  }

  /**
   * Send a command and receive answer using Query Command Protocol
   * 
   * @link http://docs.basex.org/wiki/Server_Protocol#Query_Command_Protocol
   * 
   * @param int $code
   * @param string $arg
   * @param boolean $noredirects Don't redirect output.
   * @return mixed
   * 
   * @throws BaseX\Error\SessionError
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
      throw new SessionError($this->socket->read());
    }
    
    $this->socket->clearBuffer();
    return $result;
  }
  
  protected function checkLock()
  {
    if($this->isLocked())
    {
      throw new SessionError("Session is locked.");
    }
  }
  
  /**
   * Checks whether session is flagged as locked.
   * 
   * @return boolean
   */
  public function isLocked()
  {
    return $this->locked;
  }
  
  /**
   * Flags session as locked.
   * 
   * @return \BaseX\Session $this
   */
  public function lock()
  {
    $this->locked = true;
    return $this;
  }
  
  /**
   * Flags session as unlocked.
   * 
   * @return \BaseX\Session $this
   *  
   */
  public function unlock()
  {
    $this->locked = false;
    return $this;
  }
  
  /**
   * Gets a session option.
   * 
   * @link http://docs.basex.org/wiki/Options
   * 
   * @param string $name
   * @return string
   */
  public function getOption($name)
  {
    return $this->getInfo()->option($name);
  }
  
  /**
   * Sets an option.
   * 
   * @link http://docs.basex.org/wiki/Options
   * 
   * @param string $name
   * @param mixed $value
   * @return \BaseX\Session  $this
   */
  public function setOption($name, $value)
  {
    if($value instanceof SessionInfo)
    {
      $value = $value->option($name);
    }
    
    $this->execute("SET $name \"$value\"");
    return $this;
  }
  
  /**
   * Resets an option
   * 
   * @param string $name
   * @return \BaseX\Session $this
   */
  public function resetOption($name)
  {
    $this->execute("SET $name");
    return $this;
  }
  
}
