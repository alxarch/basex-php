<?php

namespace BaseX\Session;

use BaseX\Exception;

class Socket
{
  const BUFFER_SIZE = 4096;

  protected $socket = null;
  protected $buffer = '';
  protected $pos = 0;
  protected $size = 0;
  
  public function __construct($host, $port)
  {
    $this->socket = stream_socket_client("tcp://$host:$port");
    if(false === $this->socket) 
    {
      throw new Exception("Can't communicate with server.");
    }
  }
  
  public function stream_read($size)
  {
    $chunks = array();
    
    while($size > 0)
    {
      $this->_fill();
      
      $pos = strpos($this->buffer, chr(0), $this->pos);
      
      if(false === $pos)
      {
        $actual = $this->size - $this->pos;
        
        if($actual > $size)
        {
          $chunks[] = substr($this->buffer, $this->pos, $size);
          $this->pos += $size;
          $size = 0;
        }
        else
        {
          $chunks[] = substr($this->buffer, $this->pos);
          $size -= $actual;
          $this->pos = $this->size;
        }
      }
      else
      {
        $actual = $pos - $this->pos;
        if($actual > $size)
        {
          $chunks[] = substr($this->buffer, $this->pos, $size);
          $this->pos += $size;
        }
        else
        {
          $chunks[] = substr($this->buffer, $this->pos, $actual);
          $this->pos = $pos + 1;
        }
        $size = 0;
      }
    }
    
    return implode($chunks);
  }
  
  public function read($clear=false)
  {
    if($clear)
    {
      $this->pos = $this->size;
    }
    
    $parts = array();
    
    while(true)
    {
      $this->_fill();
      $pos = strpos($this->buffer, chr(0), $this->pos);
      
      if(false === $pos)
      {
        $parts[] = substr($this->buffer, $this->pos);
        $this->pos = $this->size;
      }
      else
      {
        $parts[] = substr($this->buffer, $this->pos, $pos);
        $this->pos = $pos + 1;
        break;
      }
    }
    
    return implode($parts);
  }
  
  public function readSingle()
  {
    $this->_fill();
    
    return $this->buffer[$this->pos++];
    
  }
  
  public function clearBuffer()
  {
    $this->pos = 0;
    $this->size = 0;
  }
  
  protected function _fill()
  {
    if($this->pos >= $this->size)
    {
      $this->buffer = fread($this->socket, 4096);;
      $this->size = strlen($this->buffer);
      $this->pos = 0;
      return $this->size;
    }
    return 0;
  }
  
  public function send($msg)
  {
    $written = false;
    if(is_resource($msg))
      $written = stream_copy_to_stream ($msg, $this->socket);
    else
      $written = fwrite($this->socket, $msg, strlen($msg));
    if(false === $written)
      throw new Exception("Failed to send message.");
    return $written;
  }
  
  public function close()
  {
    return fclose($this->socket);
  }
  
} 