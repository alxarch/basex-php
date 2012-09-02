<?php

namespace BaseX;

use BaseX\Helpers as B;
use BaseX\Session;
use BaseX\Session\Socket;
use BaseX\Database;
use BaseX\Resource\Info as ResourceInfo;

class StreamWrapper
{
  
  const NAME = 'basex';
  
  /**
   *
   * @var \BaseX\Session
   */
  static protected $session;
  
  protected $path;
  
  protected $db;
  
  protected $info = null;
  
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
   * Opens a resource on BaseX
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
    
    try
    {
      $this->setMode($mode);
      $this->parsePath($path);
      $this->loadInfo();
    }
    catch(\Exception $e) 
    {
      $this->error($e->getMessage());
      return false;
    }
    
    return true;
  }
  
  /**
   * Delete shortcut.
   * 
   * @param string $path
   */
  public function unlink($path)
  {
    try
    {
      $this->parsePath($path);
      self::$session->query("db:delete('$this->db', '$this->path')")->execute();
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
  protected function stopReceiving()
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
  
  protected function startSending()
  {
    if(!$this->sending)
    {
      self::$session->execute("OPEN $this->db");
      
      if(count($this->options) || $this->parser)
      {
        $this->restore = self::$session->getInfo();
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
  }
  
  protected function stopSending()
  {
    if($this->sending)
    {
      $sock = self::$session->getSocket();
      $sock->send(Socket::NUL);
      $sock->clearBuffer();
      $error = $sock->detectError();
      $sock->clearBuffer();
      
      self::$session->unlock();
      
      // Restore options to previous values.
      
      foreach ($this->options as $name => $value)
      {
        $value = $this->restore->option($name);
        self::$session->execute("SET $name '$value'");
      }
      
      if($this->parser)
      {
        $parser = $this->restore->option('parser');
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
  
  protected function detectMethod()
  {
    if(null === $this->info)
    {
      $patterns = explode(',', self::$session->getInfo()->option('createfilter'));

      foreach ($patterns as $pattern)
      {
        if(fnmatch($pattern, $this->path))
        {
          return Session::ADD;
        }
      }
    }
    elseif(!$this->info->raw())
    {
      return Session::REPLACE;
    }
    
    return Session::STORE;
  }

  /**
   * Start receiving data and lock all needed resources.
   * 
   * Session is locked because buffer contents affect to current transmission.
   */
  protected function startReceiving()
  {
    if(!$this->receiving)
    {
      $sock = self::$session->getSocket();
      if($this->info->raw())
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
  
  protected function setMode($mode)
  {
    if('r' === $mode || 'w' === $mode)
      $this->mode = $mode;
    else
      throw new \InvalidArgumentException('Only r and w modes implemented.');
  }
  
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
      foreach (parse_str($url['query']) as $name => $value)
      {
        $this->options[strtolower($name)] = urldecode($value);
      }
    }
    
    $this->path = substr($url['path'], 1);
    $this->db = $url['host'];
  }
  
  protected function loadInfo()
  {
    $xql = sprintf("db:exists('%s')", $this->db);
    if('false' === self::$session->query($xql)->execute())
    {
      throw new \Exception('Database not found.');
    }
    
    $xql = sprintf("db:list-details('%s', '%s')", $this->db, $this->path);
    
    $data = self::$session->query($xql)->execute();
    
    if($data)
    {
      $this->info = new ResourceInfo($data);
    }
    elseif('r' === $this->mode)
    {
      throw new \Exception('Resource not found.');
    }
  }
  
  protected function error($e)
  {
    if($this->errors & STREAM_REPORT_ERRORS)
    {
      trigger_error($e, E_USER_WARNING);
    }
  }
  
  static public function register(Session $session)
  {
    self::$session = $session;
    return stream_wrapper_register(self::NAME, get_called_class());
  }
  
  static public function unregister()
  {
    return stream_wrapper_unregister(self::NAME);
  }
 
  /**
   * Helper function to build urls for BaseX stream wrapper.
   * 
   * @param string $db
   * @param string $path
   * @param string $parser
   * @param array $options
   * 
   * @return string 
   */
  static public function url($db, $path, $parser=null, $options=array())
  {
    $parts = array(self::NAME, '://', $db, '/', $path);
   
    if(is_array($options) && !empty($options))
    {
      
      $keys = array_keys($options);
      $values = array_values($options);

      array_map('strtolower', $keys);

      $options = array_combine($keys, $values);

      if(isset($options['parseopt']) && is_array($options['parseopt']))
      {
        $options['parseopt'] = B::options($options['parseopt']);
      }

      if(isset($options['htmlopt']) && is_array($options['htmlopt']))
      {
        $options['htmlopt'] = B::options($options['htmlopt']);
      }
      
      $parts[] = http_build_query($options);
    
    }
    
    if(null !== $parser)
    {
      $parts[] = "#$parser";
    }
    
    return implode($parts);
  }
  
}
