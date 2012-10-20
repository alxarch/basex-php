<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Session;

use BaseX\Error\SocketError;
use BaseX\Helpers as B;

/**
 * Socket wrapper for communicationg with a BaseX server.
 * 
 * A lot of effort has been put into correct handling of binary data as per 
 * BaseX Server Protocol specification.
 * 
 * @package BaseX 
 */
class Socket
{
  const BUFFER_SIZE = 4096;
  const TIMEOUT = 1;
  const NUL = "\x00";
  const PAD = "\xff";
  const PADDED_NUL = "\xff\x00";
  const PADDED_PAD = "\xff\xff";

  protected $socket = null;
  protected $buffer = '';
  protected $pos = 0;
  protected $size = 0;
  
  public function __construct($host, $port)
  {
    $this->socket = stream_socket_client("tcp://$host:$port");
    
    stream_set_timeout($this->socket, self::TIMEOUT);
    
    if(false === $this->socket) 
    {
      throw new SocketError("Can't communicate with server.");
    }
  }
    
  
  /**
   * Reads socket from current position until next NUL character into a stream.
   * 
   * @param resource $stream
   * @return int 
   */
  public function readInto($stream)
  {
    $total = 0;
    $eof = false;
    while($eof === false)
    {
      $chunksize = $this->_fill();
      
      $eof = strpos($this->buffer, self::NUL, $this->pos);
      
      if(false !== $eof)
      {
        $chunksize = $eof - $this->pos;
        if($this->padded($eof))
        {
          $chunksize++;
        }
      }
      
      $total += fwrite($stream, $this->readChunk($chunksize));
    }
    
    $this->pos++;
    
    return $total;
  }
  
  /**
   * Reads socket from current position until next NUL character.
   * 
   * @return string
   */
  public function read()
  {
    $chunks = array();
    $eof = false;
    while(false === $eof)
    {
      $chunksize = $this->_fill();
      
      $eof = strpos($this->buffer, self::NUL, $this->pos);
      
      if(false !== $eof)
      {
        $chunksize = $eof - $this->pos;
        if($this->padded($eof))
        {
          $chunksize++;
          $eof = false;
        }
      }
      
      $chunks[] = $this->readChunk($chunksize);
    }
    
    $this->pos++;
    
    return implode($chunks);
  }
 
  /**
   * Reads a single byte from socket.
   * 
   * @return string
   */
  public function readSingle()
  {
    $this->_fill();
    
    return $this->buffer[$this->pos++];
    
  }
  
  /**
   * Makes sure following operations use a fresh buffer.
   * 
   */
  public function clearBuffer()
  {
    $this->pos = $this->size;
  }
  
  /**
   * Makes sure the buffer is full.
   * 
   * @return int Number of bytes remaining in the buffer. 
   */
  protected function _fill()
  {
    if($this->pos >= $this->size)
    {
      // Read ahead in case of \xFF character at the end of the buffer because
      // \xFF could be part of an \xFF\x00 escape sequence.
      $chunks = array();
      $last = self::PAD;
      while($last === self::PAD)
      {
        $chunk = fread($this->socket, self::BUFFER_SIZE);
        $chunks[] = $chunk;
        $last = substr($chunk, -1);
      }
      
      $this->buffer = implode($chunks);
      
      $this->size = strlen($this->buffer);
      
      $this->pos = 0;
      
    }

    return $this->size - $this->pos;
  }
  
  /**
   * Send a message via the socket.
   * 
   * If $msg is a resource it will be piped to the socket.
   * 
   * Using a resource is preferred in cases of large data.
   * 
   * @param string|resource $msg  
   * @return int|false 
   */
  public function send($msg)
  {
    if(is_resource($msg))
    {
      // Pipe streams into each other escaping any \x00 and \xFF characters.
      $total = 0;
      $size = 0;
      while (!feof($msg) && $size !== false) 
      {
        $buffer = fread($msg, self::BUFFER_SIZE);
        $size = fwrite($this->socket, B::scrub($buffer));
        $total+= $size;
      }
      
      return $total;
    }
    else
    {
      return fwrite($this->socket, $msg);
    }
  }
  

  
  /**
   * Closes the socket.
   * 
   * @return boolean False on failure
   */
  public function close()
  {
    return fclose($this->socket);
  }

  /**
   * Reads $size data from socket.
   * 
   * This is similar to read() but is restricted by size.
   * 
   * @param int $size How many bytes to read
   * @param boolean &$data A variable to store the data into
   * 
   * @return int How many bytes read
   */
  public function stream($size, &$data)
  {
    $chunks = array();
    $total = 0;
    $eof = false;
    
    while($eof === false && $total < $size)
    {
      $chunksize = $this->_fill();
      
      $eof = strpos($this->buffer, self::NUL, $this->pos);
      
      if($eof !== false)
      {
        $chunksize = $eof - $this->pos;
      }
      
      if($chunksize + $total > $size)
      {
        $chunksize = $size - $total;
        $eof = false;
      }
      
      if($this->padded($this->pos + $chunksize))
      {
        $chunksize++;
        $eof = false;
      }
      
      $chunk = $this->readChunk($chunksize);
      $chunks[] = $chunk;
      $total += strlen($chunk);
    }
    
    if($eof !== false)
    {
      $this->pos++;
    }
    
    $data = implode($chunks);
    
    return $total;
  }
  
  public function setTimeout($seconds, $miliseconds=0)
  {
    return stream_set_timeout($this->socket, (int) $seconds, $miliseconds);
  }
  
  protected function padded($pos)
  {
    $total = 0;
    
    while($pos-- > 0 && self::PAD === $this->buffer[$pos])
    {
      $total++;
    }
    
    return $total % 2;
  }
  
  protected function readChunk($size)
  {
    $chunk = substr($this->buffer, $this->pos, $size);
    $this->pos += $size;
    return B::unscrub($chunk);
  }
    
  /**
   * Peeks into the current contents of the buffer.
   * 
   * @return string 
   */
  public function getBuffer()
  {
    $this->_fill();
    return $this->buffer;
  }
  
  /**
   * Checks current buffer contents for error response.
   * 
   * @return boolean|string The error message, false if no error exists. 
   */
  public function detectError()
  {
    $size = $this->_fill();
    
    $pos = strpos($this->buffer, "\x00\x01", $this->pos);
    if(false === $pos || $this->padded($pos))
      return false;
    
    if($pos == $size - 2)
    {
      //Command Protocol error
      $this->read();
      return $this->read();
    }
    else
    {
      //Query Command Protocol error
      $this->read();
      $this->readSingle();
      return $this->read();
    }
      
  }
} 