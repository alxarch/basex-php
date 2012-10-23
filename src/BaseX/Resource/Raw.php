<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Database;
use BaseX\Resource\Streamable;
use SimpleXMLElement;

/**
 * BaseX Resource for non xml files.
 *
 * @package BaseX 
 */
class Raw extends Streamable
{

  
  public function isRaw()
  {
    return true;
  }

  public function getFilepath($dbpath)
  {
    return sprintf("%s/%s/raw/%s", rtrim($dbpath, '\/'), $this->getDatabase(), $this->getPath());
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

  public static function fromSimpleXML(Database $db, SimpleXMLElement $xml)
  {
    $resource = parent::fromSimpleXML($db, $xml);
    $resource->setSize((int) $xml['size']);
    return $resource;
  }

}
