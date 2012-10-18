<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Resource\ResourceInterface;

/**
 * Interface for streamable resources (raw, document ...).
 *
 * @author alxarch
 */
interface StreamableInterface extends ResourceInterface
{
  
  public function isRaw();
  
  public function getContentType();
  
  public function getURI($options=array());
  
  public function getStream($mode, $options=array());
  
  public function getContents();

  public function setContents($data);
  
  public function getReadMethod();

  public function getWriteMethod();
}
