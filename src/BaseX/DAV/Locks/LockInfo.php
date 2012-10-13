<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Dav\Locks;

use Sabre_DAV_Locks_LockInfo;
use Serializable;
use BaseX\Error\UnserializationError;
use BaseX\Helpers as B;

/**
 * LockInfo (un)serializable to xml.
 *
 * @author alxarch
 */
class LockInfo extends Sabre_DAV_Locks_LockInfo implements Serializable
{
  public function serialize()
  {
    $xml = simplexml_load_string('<lock xmlns=""/>');
    $xml->addChild('created', $this->created ? $this->created : time());
    $xml->addChild('timeout', isset($this->timeout) ? $this->timeout : 3600);
    $xml->addChild('token', $this->token);
    $xml->addChild('uri', $this->uri);
    $xml->addChild('owner', isset($this->owner) ? $this->owner : 'AnonymousCoward');
    $xml->addChild('depth', isset($this->depth) ? $this->depth : 0);
    $xml->addChild('scope', isset($this->scope) ? $this->scope : LockInfo::EXCLUSIVE);
    return B::stripXMLDeclaration($xml->asXML());
  }
  
  public function unserialize($data)
  {
    $xml = @simplexml_load_string($data);
    if(false === $xml || !isset($xml->token) || !isset($xml->uri))
    {
      throw new UnserializationError();
    }
    
    $this->uri = (string) $xml->uri;
    $this->token = (string) $xml->token;
    $this->created = (int) $xml->created;
    $this->depth = (int) $xml->depth;
    $this->owner = (string) $xml->owner;
    $this->scope = (int) $xml->scope;
    $this->timeout = (int) $xml->timeout;
   
  }
}

