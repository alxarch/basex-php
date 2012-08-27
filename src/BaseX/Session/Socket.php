<?php

namespace BaseX\Session;

use BaseX\Exception;

class Socket
{
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
  
  public function read($clear=false)
  {
    if($clear)
    {
      $this->pos = 0;
      $this->size = 0;
    }
    
    $parts = array();
    
    while(true)
    {
      $this->fillBuffer();
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
    $this->fillBuffer();
    
    return $this->buffer[$this->pos++];
    
  }
  
  public function clearBuffer()
  {
    $this->pos = 0;
    $this->size = 0;
  }
  
  protected function fillBuffer()
  {
    if($this->pos >= $this->size)
    {
      $this->buffer = fread($this->socket, 4096);;
      $this->size = strlen($this->buffer);
      $this->pos = 0;
    }
  }
  
  public function send($msg)
  {
    if(false === fwrite($this->socket, $msg))
      throw new Exception("Failed to send message.");
  }
  
  public function close()
  {
    return fclose($this->socket);
  }
  
}