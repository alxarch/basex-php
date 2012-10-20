<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Dav\Locks;

use BaseX\Database;
use Sabre_DAV_Locks_Backend_Abstract as AbstractBackend;
use BaseX\Helpers as B;
use Sabre_DAV_Locks_LockInfo as LockInfo;
use BaseX\Query\Results\CallbackResults;
use BaseX\Query\QueryBuilder;

/**
 * WebDAV locks backend storing lock info in a BaseX database.
 *
 * @package SabreDAV-BaseX
 */
class Backend extends AbstractBackend
{
  
  /**
  * @var \BaseX\Database
  */
  protected $db;
  
  /**
    * The locks resource path.
    *
    * @var string
    */
  protected $locks;


  /**
    * Constructor
    *
    * @param BaseX\Database $db
    * @param string $resource
    */
  public function __construct(Database $db, $path)
  {
    $this->path = $path;
    $this->db = $db;
  }
  
  /**
    * Returns a list of Sabre_DAV_Locks_LockInfo objects
    *
    * This method should return all the locks for a particular uri, including
    * locks that might be set on a parent uri.
    *
    * If returnChildLocks is set to true, this method should also look for
    * any locks in the subtree of the uri for locks.
    *
    * @param string $uri
    * @param bool $returnChildLocks
    * @return array
    */
  public function getLocks($uri, $returnChildLocks) 
  {
    $locks = QueryBuilder::begin()
          ->setBody("db:open('$this->db', '$this->path')//*:lock")
          ->getQuery($this->db->getSession())
          ->getResults(new CallbackResults(array($this, 'unserialize')));
    
    $result = array();
    
    $now = time();
    
    foreach ($locks as $lock)
    {
      if($lock->created + $lock->timeout > $now)
      {
        if($lock->uri === $uri || false !== B::relative($uri, $lock->uri))
        {
          $result[] = $lock;
        }
        elseif ($returnChildLocks && false !== B::relative($lock->uri, $uri)) 
        {
          $result[] = $lock;
        }
      }
    }
    
    return $result;
  }

  /**
    * Locks a uri
    *
    * @param string $uri
    * @param Sabre_DAV_Locks_LockInfo $lockInfo
    * @return bool
    */
  public function lock($uri, LockInfo $lock) 
  {
    $lock->uri = trim($uri);
    $xml = $this->serialize($lock);
    $this->db->replace("$this->path/$lock->token.xml", $xml);
    return true;
  }

  /**
    * Removes a lock from a uri
    *
    * @param string $uri
    * @param Sabre_DAV_Locks_LockInfo $lockInfo
    * @return bool
    */
  public function unlock($uri, LockInfo $lock) 
  {
    $this->db->delete("$this->path/$lock->token.xml");
    return true;
  }
  
  public function serialize(LockInfo $lock)
  {
    $xml = simplexml_load_string('<lock xmlns=""/>');
    $xml->addChild('created', $lock->created ? $lock->created : time());
    $xml->addChild('timeout', isset($lock->timeout) ? $lock->timeout : 3600);
    $xml->addChild('token', $lock->token);
    $xml->addChild('uri', $lock->uri);
    $xml->addChild('owner', isset($lock->owner) ? $lock->owner : 'AnonymousCoward');
    $xml->addChild('depth', isset($lock->depth) ? $lock->depth : 0);
    $xml->addChild('scope', isset($lock->scope) ? $lock->scope : LockInfo::EXCLUSIVE);
    return $xml->asXML();
  }
  
  public function unserialize($data)
  {
    $xml = @simplexml_load_string($data);
    
    $lock = new LockInfo();
    $lock->uri = (string) $xml->uri;
    $lock->token = (string) $xml->token;
    $lock->created = (int) $xml->created;
    $lock->depth = (int) $xml->maxdepth;
    $lock->owner = (string) $xml->owner;
    $lock->scope = (int) $xml->scope;
    $lock->timeout = (int) $xml->timeout;
    return $lock;
  }

}

