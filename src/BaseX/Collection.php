<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX;


use BaseX\Collection\CollectionInterface;
use BaseX\Collection\CollectionInfo;

use BaseX\Resource;
use BaseX\Resource\ResourceInfo;
use BaseX\Resource\Raw;
use BaseX\Resource\Document;


/**
 * Description of Collection
 *
 * @author alxarch
 */
class Collection extends Resource implements CollectionInterface
{
  
  public function reloadInfo() 
  {
    $info = CollectionInfo::get($this->getSession(), $this->getDatabase(), $this->getPath());
    
    $this->info = empty($info) ? null : $info[0];
    
    return $this;
  }

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
      $this->info = new CollectionInfo();
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
      $info = new CollectionInfo();
      $info->setData($col);
      $result[] = new Collection($this->getSession(), $this->getDatabase(), $info->getPath(), $info);
    }
    foreach ($info->getXML()->contents->resource as $resource)
    {
      $info = new ResourceInfo();
      $info->setData($resource);
      if($info->isRaw())
      {
        $result[] = new Raw($this->getSession(), $this->getDatabase(), $info->getPath(), $info);
      }
      else 
      {
        $result[] = new Document($this->getSession(), $this->getDatabase(), $info->getPath(), $info);
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
    throw new Error('Not implemented.');
  }
  
  protected function getCopyQuery($path) 
  {
    throw new Error('Not implemented.');
  }
  
  protected function getMoveQuery($dest) 
  {
    throw new Error('Not implemented.');
  }
}