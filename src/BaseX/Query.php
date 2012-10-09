<?php
/**
 * @package BaseX
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX;

use BaseX\Session;
use BaseX\Session\Socket;
use BaseX\Query\QueryResultInterface;

/** 
 * Query object for a BaseX session.
 * 
 * @author BaseX Team
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * 
 * @package BaseX
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
   * @var \BaseX\Session
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
   * @param \BaseX\Session $session The session to use
   * @param string $xquery The query to execute
   * 
   */
  public function __construct(Session $session, $xquery)
  {
    $this->session = $session;
    $this->id = (int) $this->getSession()->sendQueryCommand(self::INIT, $xquery, true);
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
    $this->getSession()->sendQueryCommand(self::BIND, array($this->id, $name, $value, $type), true);
    
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
    $this->getSession()->sendQueryCommand(self::CONTEXT, array($this->id, $value, $type), true);
    
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
    return $this->getSession()->sendQueryCommand(self::EXECUTE, $this->id);
  }
  
  /**
   * Gets query info
   * @return string
   */
  public function info() 
  {
    return $this->getSession()->sendQueryCommand(self::INFO, $this->id, true);
  }
  
  /**
   * Gets query options
   * @return array
   */
  public function options()
  {
    $data = $this->getSession()->sendQueryCommand(self::OPTIONS, $this->id, true);
    
    $options = array();
    
    foreach(explode(',', $data) as $opt)
    {
      $pos = strpos($opt, '=');
      $key = substr($opt, 0, $pos);
      $value = substr($opt, $pos+1);
      if('true' === $value)
      {
        $value = true;
      }
      if('false' === $value)
      {
        $value = false;
      }
      
      $options[$key] = $value;
      
    }
    
    return $options;
  }
   
  /**
   * Gets the session for this query.
   * 
   * @return \BaseX\Session
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
  
  public function unregister()
  {
    return $this->getSession()->sendQueryCommand(self::CLOSE, $this->id);
  }
 
  public function getResults($class=null)
  {
    $options = $this->options();
    
    $results = array();
    
    if($options['method'] === 'xml' && $options['omit-xml-declaration'])
    {
      $sock = $this->getSession()->getSocket();
      $msg = sprintf('%c%d%s', self::RESULTS, $this->id, Socket::NUL);
      $sock->send($msg);
      $sock->clearBuffer();


      if(null === $class)
      {
        $class = 'BaseX\Query\QueryResult';
      }

      if(is_object($class))
      {
        $class = get_class($class);
      }
      
      while(true)
      {
    
        $type = $sock->readSingle();
        if(Socket::NUL === $type)
        {
          break;
        }
        
        $data = $sock->read();
        
        $result = $this->bindResult(new $class($this->getSession()), $data, ord($type));

        if(null === $result)
        {
          continue;
        }

        $results[] = $result;
      }

    }
    else 
    {
      $results[] = $this->bindResult(new $class(), $this->execute());
    }
    return $results;
  }
  
  protected function bindResult(QueryResultInterface $result, $data, $type = null)
  {
    if(null === $type)
    {
      $result->setData($data);
      return $result;
    }
    try
    {
      $result->setType($type);
      $result->setData($data);
      return $result;
    }
    catch (InvalidArgumentException $e)
    {
      return null;
    }
    
  }
}