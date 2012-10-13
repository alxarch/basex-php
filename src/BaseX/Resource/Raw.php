<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Resource\Streamable;

/**
 * BaseX Resource for non xml files.
 *
 * @package BaseX 
 */
class Raw extends Streamable
{
  protected $size;

  public function isRaw() {
    return true;
  }
  
  public function setSize($size)
  {
    $this->size = (int)$size;
  }

  public function getSize() {
    return $this->size;
  }
  
  public function getFilepath()
  {
    $db = $this->getDatabase();
    $dbpath = $db->getSession()->getInfo()->dbpath;
    $path = $this->getPath();
    return "$dbpath/$db/raw/$path";
  }
  
  public function getLocalStream($mode='r')
  {
    return fopen($this->getFilePath(), $mode);
  }
  
  public function creationMethod() {
    return 'store';
  }
  
}
