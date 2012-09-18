<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Collection;

use BaseX\Query\SimpleXMLResult;
use BaseX\Query\QueryBuilder;
use BaseX\Session;

/**
 * Description of CollectionInfo
 *
 * @author alxarch
 */
class CollectionInfo extends SimpleXMLResult
{
  
  public function setData($data) 
  {
    parent::setData($data);
    if(isset($this->data['modified-date']) && isset($this->data['path']) && isset($this->data->contents))
    {
      return $this;
    }
    
    throw new \InvalidArgumentException('Invalid collection data provided');
  }
  
  public static function get(Session $session, $db, $path = null)
  {
    $xql = <<<XQL
declare function local:collection(\$db, \$path){
  let \$resources :=   db:list-details(\$db, \$path)
  let \$start := if('' eq \$path) then 1 else string-length(\$path) + 2
  let \$modified := max(\$resources/@modified-date/string())
  return
  <collection modified-date="{\$modified}" path="{substring(\$path, 2)}">
    <contents>
      {
      for \$r in \$resources
        let \$p := substring(\$r/text(), \$start)
        let \$parts := tokenize(\$p, '/')
        let \$name := \$parts[1]
        let \$count := count(\$parts)
        group by \$name
        order by -sum(\$count), \$name
        return if(\$count > 1)
          then local:collection(\$db, \$path||'/'||\$name)
          else \$r
      }
    </contents>
  </collection>
};

local:collection(\$db, \$path)
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
    return (string) $this->data['path'];
  }
  
  public function getModifiedDate()
  {
    return (string) $this->data['modified-date'];
  }
}
