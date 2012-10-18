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
use BaseX\Query\QueryResultsInterface;
use BaseX\Query\QueryResults;

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

  const TYPE_FUNCTION = 7;
  const TYPE_NODE = 8;
  const TYPE_TEXT = 9;
  const TYPE_INSTRUCTION = 10;
  const TYPE_ELEMENT = 11;
  const TYPE_DOCUMENT = 12;
  const TYPE_DOCUMENT_ELEMENT = 13;
  const TYPE_ATTRIBUTE = 14;
  const TYPE_COMMENT = 15;
  
  const  TYPE_ITEM = 32;
  const  TYPE_UNTYPED = 33;
  const  TYPE_ANYTYPE = 34;
  const  TYPE_ANYSIMPLETYPE = 35;
  const  TYPE_ANYATOMICTYPE = 36;
  const  TYPE_UNTYPEDATOMIC = 37;
  const  TYPE_STRING = 38;
  const  TYPE_NORMALIZEDSTRING = 39;
  const  TYPE_TOKEN = 40;
  const  TYPE_LANGUAGE = 41;
  const  TYPE_NMTOKEN = 42;
  const  TYPE_NAME = 43;
  const  TYPE_NCNAME = 44;
  const  TYPE_ID = 45;
  const  TYPE_IDREF = 46;
  const  TYPE_ENTITY = 47;
  const  TYPE_FLOAT = 48;
  const  TYPE_DOUBLE = 49;
  const  TYPE_DECIMAL = 50;
  const  TYPE_PRECISIONDECIMAL = 51;
  const  TYPE_INTEGER = 52;
  const  TYPE_NONPOSITIVEINTEGER = 53;
  const  TYPE_NEGATIVEINTEGER = 54;
  const  TYPE_LONG = 55;
  const  TYPE_INT = 56;
  const  TYPE_SHORT = 57;
  const  TYPE_BYTE = 58;
  const  TYPE_NONNEGATIVEINTEGER = 59;
  const  TYPE_UNSIGNEDLONG = 60;
  const  TYPE_UNSIGNEDINT = 61;
  const  TYPE_UNSIGNEDSHORT = 62;
  const  TYPE_UNSIGNEDBYTE = 63;
  const  TYPE_POSITIVEINTEGER = 64;
  const  TYPE_DURATION = 65;
  const  TYPE_YEARMONTHDURATION = 66;
  const  TYPE_DAYTIMEDURATION = 67;
  const  TYPE_DATETIME = 68;
  const  TYPE_DATETIMESTAMP = 69;
  const  TYPE_DATE = 70;
  const  TYPE_TIME = 71;
  const  TYPE_GYEARMONTH = 72;
  const  TYPE_GYEAR = 73;
  const  TYPE_GMONTHDAY = 74;
  const  TYPE_GDAY = 75;
  const  TYPE_GMONTH = 76;
  const  TYPE_BOOLEAN = 77;
  const  TYPE_BINARY = 78;
  const  TYPE_BASE64BINARY = 79;
  const  TYPE_HEXBINARY = 80;
  const  TYPE_ANYURI = 81;
  const  TYPE_QNAME = 82;
  const  TYPE_NOTATION = 83;

  
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
  
  public function getFirstResult(MapperInterface $mapper=null)
  {
    $results = $this->getResults($mapper);
    return (count($results) > 0) ? $results[0] : null;
  }
  
  public function getSingleResult(MapperInterface $mapper=null)
  {
    $results = $this->getResults($mapper);
    return (count($results) === 1) ? $results[0] : null;
  }
 
  /**
   * 
   * @param \BaseX\Query\Result\QueryResultsInterface $results
   * @return \BaseX\Query\Result\QueryResultsInterface
   * @throws \InvalidArgumentException
   */
  public function getResults(QueryResultsInterface $results=null)
  {
    if(null === $results)
    {
      $results = new QueryResults();
    }
    else
    {
      $options = $this->options();
      $method = $options['method'];
      if(!$results->supportsMethod($options['method']))
      {
        throw new \InvalidArgumentException("Results container does not support '$method' serialization method.");
      }
    }
     
    $sock = $this->getSession()->getSocket();
    $msg = sprintf('%c%d%s', self::RESULTS, $this->id, Socket::NUL);
    $sock->send($msg);
    $sock->clearBuffer();

    while(true)
    {

      $byte = $sock->readSingle();

      if(Socket::NUL === $byte)
      {
        break;
      }

      $data = $sock->read();
      $type = ord($byte);

      $results->addResult($data, $type);
    }
    
    return $results;
    
  }
  
}