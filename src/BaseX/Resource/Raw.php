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
use BaseX\Database;

/**
 * BaseX Resource for non xml files.
 *
 * @package BaseX 
 */
class Raw extends Streamable
{

  protected $size;

  public function isRaw()
  {
    return true;
  }

  public function setSize($size)
  {
    $this->size = (int) $size;
  }

  public function getSize()
  {
    return $this->size;
  }

  public function getFilepath()
  {
    $db = $this->getDatabase();
    $dbpath = $db->getSession()->getInfo()->dbpath;
    $path = $this->getPath();
    return "$dbpath/$db/raw/$path";
  }

  public function getReadMethod()
  {
    return 'retrieve';
  }

  public function getWriteMethod()
  {
    return 'store';
  }

  public function getContents()
  {
    $command = sprintf('RETRIEVE "%s"', $this->getPath());
    return $this->getDatabase()->execute($command);
  }

  public function setContents($data)
  {
    $this->getDatabase()->store($this->getPath(), $data);
  }

  public static function fromSimpleXML(Database $db, \SimpleXMLElement $xml)
  {
    $resource = parent::fromSimpleXML($db, $xml);
    $resource->setSize((int) $xml['size']);
    return $resource;
  }

}
