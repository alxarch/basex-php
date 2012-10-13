<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource\Interfaces;

use BaseX\Resource\Interfaces\ResourceInterface;

/**
 * Interface for streamable resources (raw, document ...).
 *
 * @author alxarch
 */
interface StreamableResource extends ResourceInterface
{
  
  public function isRaw();
  
  public function getContentType();
  
  public function getURI();
  
  public function getStream($mode);
  
  public function write($data);
  
  public function read();
  
  public function creationMethod();
}
