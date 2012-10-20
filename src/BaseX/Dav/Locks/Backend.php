<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Dav\Locks;

use BaseX\Database;
use BaseX\Query\Results\UnserializableResults;
use Sabre_DAV_Locks_Backend_Abstract as AbstractBackend;
use BaseX\Dav\Locks\LockInfo;
use BaseX\Helpers as B;
use Sabre_DAV_Locks_LockInfo;

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
    
    $childlocks = B::value((boolean)$returnChildLocks);
    
    $xql = <<<XQL
 
let \$uri := '$uri'
let \$childlocks := $childlocks
let \$now := convert:dateTime-to-ms(current-dateTime())
let \$parts :=  tokenize(\$uri, '/')
let \$parents := 
  for \$part at \$i in \$parts
    return string-join(subsequence(\$parts, 0, \$i), '/')
    
return 

for \$lock in db:open('$this->db', '$this->path')//lock
  let \$expires := (xs:integer(\$lock/created) + xs:integer(\$lock/timeout)) * 1000
  let \$parentlock :=  \$lock/depth != 0 and \$lock/uri = \$parents
  let \$childlock := \$childlocks and starts-with(\$lock/uri, \$uri || '/')
  
  where \$expires > \$now and (\$lock/uri = \$uri or \$parentlock or \$childlock)
   
  return \$lock

XQL;
    
    return $this->db->getSession()->query($xql)->getResults(new UnserializableResults(new LockInfo));
  }

  /**
    * Locks a uri
    *
    * @param string $uri
    * @param Sabre_DAV_Locks_LockInfo $lockInfo
    * @return bool
    */
  public function lock($uri, Sabre_DAV_Locks_LockInfo $lock) 
  {
    if(!$lock instanceof LockInfo)
    {
      throw new \InvalidArgumentException('Invalid lock class.');
    }
    
    $lock->uri = $uri;
    
    $xml = $lock->serialize();
    $this->db->replace("$this->path/$uri/lock.xml", $xml);
    return true;
  }

  /**
    * Removes a lock from a uri
    *
    * @param string $uri
    * @param Sabre_DAV_Locks_LockInfo $lockInfo
    * @return bool
    */
  public function unlock($uri, Sabre_DAV_Locks_LockInfo $lock) 
  {
    $this->db->delete("$this->path/$uri/lock.xml");
    return true;
  }

}

