<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX;

use BaseX\Helpers as B;
use BaseX\Session;
use BaseX\Session\Socket;
use BaseX\Resource\Streamable;

/**
 * Stream wrapper for BaseX resources
 * 
 * @package BaseX 
 */
class StreamWrapper
{
  /**
   * Used for the scheme part of the url.
   * 
   * ie basex://database/example.xml 
   */
  const NAME = 'basex';
  
  /**
   * Stream context
   * 
   * Not currently used.
   * 
   * @var resource
   */
  public $context;
  
  /**
   * The session to use for all requests.
   * 
   * @var \BaseX\Session
   */
  static protected $session;
  
  protected $path;
  
  protected $db;
  
  /**
   *
   * @var \BaseX\Resource\Interfaces\StreamableInterface
   */
  protected $resource = null;
  
  protected $eof = false;
  
  protected $mode;
  
  protected $receiving = false;
  
  protected $sending = false;
  
  protected $buffer = '';
  
  protected $parser = null;
  
  protected $options = array();
  
  protected $errors = false;

  protected $replace = false;
  
  protected $restore = null;
  
  /**
   * Opens a resource on BaseX server as a stream
   * 
   * In write mode any options (ie CHOP, HTMLOPT etc) can be passed via query
   * string in the url. 
   * 
   * If a specific parser is required it can be specified through hash value.
   * 
   * For example:
   * 
   *   basex://database/path/to/file.xml?chop=true
   *   basex://database/path/to/file.json#json
   *   basex://database/path/to/file.json?parseropt=jsonml%3Dtrue#json
   * 
   * @param string $path Use basex://{db}/{path}
   * @param string $mode Supported modes: r, w
   * @param int $options If STREAM_REPORT_ERRORS is set it will trigger errors.
   * @param string $opath Ignored
   * 
   * @return boolean Successfully created handler
   */
  public function stream_open($path, $mode, $options, &$opath)
  {
    $this->errors = $options & STREAM_REPORT_ERRORS;
    if(self::$session->isLocked())
      $this->error('Session is locked.');
    
    try
    {
      $this->setMode($mode);
      $this->parsePath($path);
      $this->loadResource();
    }
    catch(\Exception $e) 
    {
      $this->error($e->getMessage());
      return false;
    }
    
    return true;
  }
  
  /**
   * Deletes a resource on basex.
   * 
   * @param string $path
   */
  public function unlink($path)
  {
    try
    {
      $this->parsePath($path);
      $this->db->delete($this->path);
    }
    catch(\Exception $e)
    {
      $this->error($e->getMessage());
      return false;
    }
    
    return true;
  }
  
  /**
   * Tests for end-of-file.
   * 
   * @return boolean
   */
  public function stream_eof()
  {
    return true === $this->eof;
  }
  
  /**
   * Write to a resource.
   * 
   * This method is called in response to fwrite().
   * 
   * @param string $data
   * 
   * @return int 
   */
  public function stream_write($data)
  {
    if($this->mode !== 'w')
    {
      $this->error('Resource is not opened in write mode.');
      return false;
    }
    
    $this->startSending();
    
    $clean = B::scrub($data);
    
    $written = self::$session->getSocket()->send($clean);
    
    if(false === $written)
      return 0; 
      
    return strlen ($data);
  }
  
  public function stream_stat()
  {
    $mode = 0100000 + (($this->mode === 'w') ? 0666 : 0444);
    $size = $this->resource !== null && method_exists($this->resource, 'getSize') ? $this->resource->getSize() : 0;
    $mtime = $this->resource !== null ? (int)$this->resource->getModified()->format('U') : 0;
    
    $values = array(
      0  => 0 ,
      1  => 0,
      2  => $mode,
      3  => 1,
      4  => 0,
      5  => 0,
      6  => 0,
      7  => $size,
      8  => $mtime,
      9  => $mtime,
      10 => $mtime,
      11 => -1,
      12 => -1,
    );
    $keys = array(
      'dev',
      'ino',
      'mode' ,
      'nlink',
      'uid' ,
      'gid' ,
      'rdev' ,
      'size' ,
      'atime' ,
      'mtime' ,
      'ctime' ,
      'blksize' ,
      'blocks'
    );
    
    return array_merge($values, array_combine($keys, $values));
  }

  /**
   * Read from stream.
   * 
   * This method is called in response to fread() and fgets().
   * 
   * @param int $count
   * @return string|boolean If there are less than $count bytes available, 
   * return as many as are available. If no more data is available, return 
   * either FALSE or an empty string.
   */
  public function stream_read($count)
  {
    if($this->mode !== 'r')
    {
      $this->error('Resource is not opened in read mode.');
      return false;
    }
    
    $this->startReceiving();
    
    if($this->eof)
    {
      return false;
    }
    
    $total = self::$session->getSocket()->stream($count, $this->buffer);
    
    if($total < $count)
    {
      $this->eof = true;
    }
    
    return $this->buffer;
  }
  
  /**
   * Close this resource.
   * 
   * This method is called in response to fclose().
   * 
   * @return boolean 
   */
  public function stream_close()
  {
    $this->stopReceiving();
    return $this->stopSending();
  }
  
  /**
   * Stop receiving data and unlock all resources.
   * 
   */
  public function stopReceiving()
  {
    if($this->receiving)
    {
      $sock = self::$session->getSocket();
      
      if(!$this->eof)
      {
        $sock->read();
      }
      
      $sock->read();
      
      $sock->clearBuffer();
      self::$session->unlock();
      
      $this->eof = true;
      
      $this->receiving = false;
      
      // reset serializer method.
      self::$session->execute("SET SERIALIZER");
    }
  }
  
  /**
   * Locks session and starts receiving data.
   */
  public function startSending()
  {
    if(true === $this->sending) return;
    
    self::$session->execute("OPEN $this->db");

    if(count($this->options) || $this->parser)
    {
      $this->restore = self::$session->getOptions();
    }

    foreach ($this->options as $name => $value)
    {
      self::$session->execute("SET $name '$value'");
    }

    $method = $this->detectMethod();

    if($this->parser && Session::ADD === $method)
    {
      self::$session->execute("SET PARSER $this->parser");
    }
    self::$session->lock();
    $sock = self::$session->getSocket();
    $msg = sprintf('%c%s%s', $method, $this->path, Socket::NUL);
    $sock->send($msg);
    $this->sending = true;
  }
  
  /**
   * Stops sendong data and unlocks session.
   */
  public function stopSending()
  {
    if($this->sending)
    {
      $sock = self::$session->getSocket();
      $sock->send(Socket::NUL);
      $sock->clearBuffer();
      $error = $sock->detectError();
      $sock->clearBuffer();
      
      self::$session->unlock();
      self::$session->execute('CLOSE');
      // Restore options to previous values.
      
      foreach ($this->options as $name => $value)
      {
        $value = $this->restore[strtoupper($name)];
        self::$session->execute("SET $name '$value'");
      }
      
      if($this->parser)
      {
        $parser = $this->restore['PARSER'];
        self::$session->execute("SET parser $parser");
      }
      
      $this->sending = false;
      
      if($error)
      {
        $this->error($error);
        return false;
      }
      
    }
    return true;
  }
  
  /**
   * Auto-detect method to use for storing a document.
   */
  protected function detectMethod()
  {
    if(null === $this->resource)
    {
      $name = basename($this->path);

      if(self::$session->refresh()->matchesCreatefilter($name))
      {
        return Session::REPLACE;
      }
    }
    elseif('replace' === $this->resource->getWriteMethod())
    {
      return Session::REPLACE;
    }
    
    return Session::STORE;
  }

  /**
   * Lock session and start receiving data.
   * 
   * Session is locked because buffer contents affect to current transmission.
   */
  public function startReceiving()
  {
    if(!$this->receiving)
    {
      $sock = self::$session->getSocket();
      if($this->resource->isRaw())
      {
        self::$session->execute("OPEN $this->db");
        self::$session->execute("SET SERIALIZER raw");
        $msg = "RETRIEVE $this->path";
      }
      else
      {
        $xql = sprintf("db:open('%s','%s')", $this->db, $this->path);
        $id = self::$session->query($xql)->getId();
        $msg = sprintf('%c%s', Query::EXECUTE, $id);
      }
      
      $sock->clearBuffer();
      $sock->send($msg.Socket::NUL);
      
      
      self::$session->lock();
      
      $this->eof = false;
      
      $this->receiving = true;
      
      $error = $sock->detectError();
      
      if($error)
      {
        self::$session->unlock();
        
        $sock->clearBuffer();
        
        $this->error($error);
        
        $this->receiving = false;
      }
    }
  }
  
  /**
   * Set stream mode.
   * 
   * Valid modes: r, w
   * 
   * @param string $mode
   * 
   * @throws \InvalidArgumentException 
   */
  protected function setMode($mode)
  {
    if('r' === $mode || 'w' === $mode)
      $this->mode = $mode;
    else
      throw new \InvalidArgumentException('Only r and w modes implemented.');
  }
  
  /**
   * Parses a uri.
   * 
   * @param string $path
   * 
   * @throws \InvalidArgumentException If no database and/or document is 
   * specified in the url
   */
  protected function parsePath($path)
  {
    $url = parse_url($path);
    if(!isset($url['path']) || '/' === $url['path'])
      throw new \InvalidArgumentException('No document specified in url.');
    
    if(!isset($url['host']))
     throw new \InvalidArgumentException('No database specified in url.');
    
    if(isset($url['fragment']))
    {
      $this->parser = isset($url['fragment']);
    }
    
    if(isset($url['query']))
    {
      $params = array();
      parse_str($url['query'], $params);
      foreach ($params as $name => $value)
      {
        $this->options[strtolower($name)] = urldecode($value);
      }
    }
    
    $this->path = substr($url['path'], 1);
    $this->db = new Database(self::$session, $url['host']);
  }
  
  /**
   * Loads resource info.
   * 
   * @throws BaseX\Error If mode is 'r' and resource does not exist.
   */
  protected function loadResource()
  {
    
    $resource = $this->db->getResource($this->path);
    
    if(null === $resource)
    {
      if('r' === $this->mode)
      {
        throw new Error('Resource not found.');
      }
    }
    else
    {
      $this->setResource($resource);
    }
  }
  
  /**
   * Handle errors.
   * 
   * @param string $msg Error message
   */
  protected function error($msg)
  {
    if($this->errors & STREAM_REPORT_ERRORS)
    {
      trigger_error($msg, E_USER_WARNING);
    }
  }
  
  /**
   * Registers stream wrapper to handle basex:// urls.
   * 
   * @uses stream_wrapper_register()
   * 
   * @param Session $session
   * @return bool true on success false on failure.
   */
  static public function register(Session $session)
  {
    self::$session = $session;
    return stream_wrapper_register(self::NAME, get_called_class());
  }
  
  /**
   * Unregisters stream wrapper from handling of basex:// urls.
   * 
   * @uses stream_wrapper_register()
   * 
   * @return bool 
   */
  static public function unregister()
  {
    return stream_wrapper_unregister(self::NAME);
  }
  
  protected function setResource(Streamable $resource)
  {
    $this->resource = $resource;
  }
}
