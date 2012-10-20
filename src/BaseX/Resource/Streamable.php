<?php

/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Resource\StreamableInterface;
use BaseX\Resource;
use BaseX\Helpers as B;
use BaseX\Error;
use BaseX\Database;

/**
 * Base class for streamable resources (raw/document).
 *
 * @author alxarch
 */
abstract class Streamable extends Resource implements StreamableInterface
{

  /**
   *
   * @var int
   */
  
  protected $size;

  /**
   * Content-Type for this resource.
   * 
   * @var string
   */
  protected $mime;

  public function getUri($options = array())
  {
    $parser = isset($options['parser']) ? $options['parser'] : null;
    return B::uri($this->getDatabase(), $this->getPath(), $parser, $options);
  }

  /**
   * Return a stream handler for this resource.
   * 
   * @param string $mode valid modes: r, w
   * @return resource
   * 
   * @throws Error 
   */
  public function getStream($mode = 'r', $options = array())
  {
    $uri = $this->getUri($options);

    $stream = @fopen($uri, $mode);

    if (false === $stream)
    {
      throw new Error('Failed to open resource stream.');
    }

    return $stream;
  }

  /**
   * Content-Type for this resource.
   * 
   * @return string
   */
  public function getContentType()
  {
    return $this->mime;
  }

  /**
   * Set mime type for this resource.
   * 
   * @param string $type
   * @return \BaseX\Resource\Streamable
   */
  public function setContentType($type)
  {
    $this->mime = $type;
    return $this;
  }

  /**
   * Refreshes info for this resource.
   * 
   * @return \BaseX\Resource\Streamable Returns itself on success. 
   * NULL is returned if resource is no longer available or has changed 
   * from Raw to Document or vice versa.
   */
  public function refresh()
  {
    $res = $this->getDatabase()->getResource($this->getPath());

    if (null !== $res && $this->isRaw() === $res->isRaw())
    {
      $this->path = $res->getPath();
      $this->modified = $res->getModified();
      $this->mime = $res->getContentType();
      if (method_exists($res, 'getSize') && method_exists($this, 'setSize'))
      {
        $this->setSize($res->getSize());
      }

      return $this;
    }
    else
    {
      return null;
    }
  }

  public static function fromSimpleXML(Database $db, \SimpleXMLElement $xml)
  {
    $resource = parent::fromSimpleXML($db, $xml);
    $resource->setContentType((string) $xml['content-type']);
    return $resource;
  }
  
  public function setSize($size)
  {
    $this->size = (int) $size;
  }

  public function getSize()
  {
    return $this->size;
  }

}

