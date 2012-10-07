<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Collection;

use BaseX\Query\SimpleXMLResult;
use BaseX\Query\QueryBuilder;
use BaseX\Session;

/**
 * Query result wrapper for Collection Info
 *
 * @author alxarch
 */
class CollectionInfo extends SimpleXMLResult
{
  
  public function setData($data) 
  {
    parent::setData($data);
    if(isset($this->xml['modified-date']) && isset($this->xml['path']) && isset($this->xml->contents))
    {
      return $this;
    }
    
    throw new \InvalidArgumentException('Invalid collection data provided');
  }
  
  public static function get(Session $session, $db, $path = null)
  {
    $xql = <<<XQL
declare function local:relative-path(\$path, \$base){
  if(\$base ne '') then substring(\$path, string-length(\$base) + 2) else \$path
};

declare function local:trim-path(\$path){
  if(starts-with(\$path, '/')) then substring(\$path, 2) else \$path
};

declare function local:collection(\$db, \$path){
  let \$path := local:trim-path(\$path)
  let \$resources := db:list-details(\$db, \$path)
  return
  if(empty(\$resources)) then () else
    let \$modified := max(\$resources/@modified-date/string())
    return 
      <collection modified-date="{\$modified}" path="{ \$path }">
        <contents>
          {
          for \$r in \$resources
            let \$rel-path := local:relative-path(\$r/text(), \$path)
            let \$name := substring-before(\$rel-path, '/')
            group by \$name
            order by \$name
            return if(\$name)
              then local:collection(\$db, \$path||'/'||\$name)
              else \$r
          }
        </contents>
      </collection>
};

local:collection('$db', '$path')
XQL;
    
    return QueryBuilder::begin()
      ->setBody($xql)
      ->addExternalVariable('db', $db)
      ->addExternalVariable('path', $path)
      ->getQuery($session)
      ->getResults(get_called_class());
  }
  
  public function getPath()
  {
    return (string) $this->xml['path'];
  }
  
  public function getModifiedDate()
  {
    return (string) $this->xml['modified-date'];
  }
}
