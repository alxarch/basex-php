<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Dav\Locks;

use BaseX\Session;

use Sabre_DAV_Locks_Backend_Abstract as AbstractBackend;
use Sabre_DAV_Locks_LockInfo as LockInfo;


/**
 * WebDAV locks backend storing lock info in a BaseX database.
 *
 * @package SabreDAV-BaseX
 */
class Backend extends AbstractBackend
{
 /**
  * @var BaseX\Session
  */
  protected $session;
  
  /**
  * @var string
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
  public function __construct(Session $session, $db, $locks)
  {
    $this->session = $session;
    $this->locks = $locks;
    $this->db = $db;
    
    $xql = "if(db:exists('$db', '$locks')) then () else db:add('$db', '<locks/>', '$locks')";
    $session->query($xql)->execute();
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
    
    $childlocks = $returnChildLocks ? 'true' : 'false';
    $xql = <<<XQL
let \$uri := '$uri'
let \$childlocks := $childlocks()
let \$now := convert:dateTime-to-ms(current-dateTime())
let \$parts :=  tokenize(\$uri, '/')
let \$parents := 
  for \$part at \$i in \$parts
    return string-join(subsequence(\$parts, 0, \$i), '/')
    
return 
<locks>{
for \$lock in db:open('$this->db', '$this->locks')//lock
  let \$expires := (xs:integer(\$lock/@created/string()) + \$lock/@timeout) * 1000
  let \$parentlock :=  \$lock/@depth != 0 and \$lock/@uri/string() = \$parents
  let \$childlock := \$childlocks and starts-with(\$lock/@uri, \$uri || '/')
  where \$expires > \$now and (\$lock/@uri = \$uri or \$parentlock or \$childlock)
   
  return \$lock
}</locks> 
XQL;
    
    $locks = array();
    $data = $this->session->query($xql)->execute();
    $xml = simplexml_load_string($data);
    foreach ($xml->lock as $lock)
    {
      $info = new LockInfo();
      $info->created = (int)$lock['created'];
      $info->depth = (int)$lock['depth'];
      $info->owner = $lock['owner'];
      $info->uri = $lock['uri'];
      $info->token = $lock['token'];
      $info->scope = (int)$lock['scope'];
      $info->timeout = (int)$lock['timeout'];
      $locks[] = $info;
    }
    
    return $locks;
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

    $lock->uri = $uri;
    
    $token = $lock->token;
    $created = $lock->created ? $lock->created : time();
    $timeout = isset($lock->timeout) ? $lock->timeout : 3600;
    $owner = $lock->owner ? $lock->owner : 'AnonymousCoward';
    $depth = isset($lock->depth) ? $lock->depth : 0;
    $scope = isset($lock->scope) ? $lock->scope : LockInfo::EXCLUSIVE;
    
    $node = <<<XML
    <lock token="$token" 
        uri="$uri" 
        owner="$owner" 
        created="$created" 
        timeout="$timeout"
        depth="$depth" 
        scope="$scope"/>
XML;
    
    $db = $this->db;
    $path = $this->locks;
    $token = $lock->token;
    
    $xql = <<<XQL
      db:output("OK"),
      delete node db:open('$db', '$path')//lock[@token eq '$token'],
      insert node $node into db:open('$db', '$path')/locks
XQL;
    
    $response = $this->session->query($xql)->execute();

    return "OK" === $response;
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
    $db = $this->db;
    $path = $this->locks;
    $token = $lock->token;
    $xql =  <<<XQL
      let \$lock := db:open('$db', '$path')//lock[@token eq '$token']
      return 
      db:output(count(\$lock) > 0),
      delete node db:open('$db', '$path')//lock[@token eq '$token']
XQL;
    
    $response = $this->session->query($xql)->execute();
    
    return "true" === $response;
  }

}

