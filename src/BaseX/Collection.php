<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX;


use BaseX\Collection\CollectionInterface;
use BaseX\Collection\CollectionInfo;

use BaseX\Resource;
use BaseX\Resource\ResourceInfo;
use BaseX\Resource\Raw;
use BaseX\Resource\Document;


/**
 * BaseX Collection
 *
 * @author alxarch
 */
class Collection extends Resource implements CollectionInterface
{
  /**
   * Reloads collection info.
   * 
   * @return \BaseX\Collection $this
   */
  public function reloadInfo() 
  {
    $info = CollectionInfo::get($this->getSession(), $this->getDatabase(), $this->getPath());
    
    $this->info = empty($info) ? null : $info[0];
    
    return $this;
  }

  /**
   * Set collection info.
   * 
   * @param \BaseX\Collection\CollectionInfo $info
   * @return \BaseX\Collection $this
   */
  public function setInfo($info)
  {
    if(null === $info)
    {
      $this->info = null;
    }
    elseif ($info instanceof CollectionInfo) 
    {
      $this->info = $info;
    }
    else
    {
      $this->info = new CollectionInfo($this->session);
      $this->info->setData($info);
    }
    
    return $this;
    
  }

  public function getSize()
  {
    return 0;
  }
  
  public function getType()
  {
    return 'collection';
  }
  
  public function isRaw()
  {
    return false;
  }
  
  /**
   * Lists all collections & resources for this collection.
   * 
   * @return array
   */
  public function listContents()
  {
    $info = $this->getInfo();
    if(null === $info)
    {
      return array();
    }
    
    $result = array();
    
    foreach ($info->getXML()->contents->collection as $col)
    {
      $inf = new CollectionInfo($this->session);
      $inf->setData($col);
      $result[] = new Collection($this->getSession(), $this->getDatabase(), $inf->getPath(), $inf);
    }
    
    foreach ($info->getXML()->contents->resource as $resource)
    {
      $inf = new ResourceInfo($this->session);
      $inf->setData($resource);
      if($inf->isRaw())
      {
        $result[] = new Raw($this->getSession(), $this->getDatabase(), $inf->getPath(), $inf);
      }
      else 
      {
        $result[] = new Document($this->getSession(), $this->getDatabase(), $inf->getPath(), $inf);
      }
    }
    
    return $result;
  }
  
  /**
   * Gets all resources for this collection.
   * 
   * @param string $path list resources from this subpath
   * @return array A BaseX\Resource array
   */
  public function getResources($path=null)
  {
    if(null === $path)
    {
      $path = $this->getPath();
    }
    else
    {
      $path = rtrim($this->getPath().'/'.trim($path, '/'), '/');
    }
    
    $resources = ResourceInfo::get($this->getSession(), $this->getDatabase(), $path);
    
    $result = array();
    foreach ($resources as $resource)
    {
      if($resource->isRaw())
      {
        $result[] = new Raw($this->getSession(), $this->getDatabase(), $resource->getPath());
      }
      else
      {
        $result[] = new Document($this->getSession(), $this->getDatabase(), $resource->getPath());
      }
    }

    return $result;
  }

  protected function getContentsQuery() 
  {
    throw new Error('Not implemented.');
  }

  protected function getDeleteQuery()
  {
    $db = $this->getDatabase();
    $src = $this->getPath();
    
    $xql = <<<XQL
      for \$resource in db:list('$db', '$src')
        return db:delete('$db', \$resource)
XQL;
    
    return $this->getSession()->query($xql);
  }
  
  protected function getCopyQuery($dest) 
  {
    $db = $this->getDatabase();
    $src = $this->getPath();
    
    $xql = <<<XQL
      for \$resource in db:list-details('$db', '$src')
        let \$src := \$resource/text()
        let \$dest := replace(\$src, '^$src', '$dest')
        return 
        if(\$resource/@raw = 'true') 
        then 
          db:store('$db', \$dest, db:retrieve('$db', \$src))
        else
          db:replace('$db', \$dest, db:open('$db', \$src))
XQL;
    
    return $this->getSession()->query($xql);
   
  }
  
  protected function getMoveQuery($dest) 
  {
    $db = $this->getDatabase();
    $src = $this->getPath();
    
    $xql = <<<XQL
      for \$resource in db:list-details('$db', '$src')
        let \$src := \$resource/text()
        let \$dest := replace(\$src, '^$src', '$dest')
        return (
          if(\$resource/@raw = 'true') 
            then 
              db:store('$db', \$dest, db:retrieve('$db', \$src))
            else
              db:replace('$db', \$dest, db:open('$db', \$src))
          ,
          db:delete('$db', \$src)
        )
XQL;
    
    return $this->getSession()->query($xql);
  }
  
  /**
   * Checks collection contents for a child.
   * 
   * @param string $name
   * @return boolean
   */
  public function hasChild($name)
  {
    $exists = $this->getPath().'/'.addcslashes($name, '/');
    $db = $this->getDatabase();
    
    return 'false' === $this->getSession()
            ->query("empty(db:list('$db', '$exists'))")
            ->execute();
  }
  
  public function addChild($name, $data, $raw = false) 
  {
    
    $path = $this->getPath().'/'.$name;
    
    $this->getSession()->execute('OPEN '. $this->getDatabase());
    
    if($raw)
    {
      $this->getSession()->store($path, $data);
    }
    else
    {
      $this->getSession()->replace($path, $data);
    }
    
    return $this;
  }
  
  public function rename($name)
  {
    $db = $this->getDatabase();
    $from = $this->getPath();
    $to = dirname($this->getPath()) . '/' . $name;
    $this->getSession()->query("db:rename('$db', '$from', '$to')")->execute();
    $info = CollectionInfo::get($this->getSession(), $db, $to);
    if(!empty($info))
    {
      $this->setInfo($info[0]);
    }
    else
    {
      $this->setInfo(null);
    }
    
    return $this;
  }
  
  public function delete() 
  {
    $db = $this->getDatabase();
    $path = $this->getPath();
    $this->getSession()->query("db:delete('$db', '$path')")->execute();
    $this->setInfo(null);
    $this->setPath(null);
  }
}