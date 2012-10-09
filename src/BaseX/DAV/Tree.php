<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\DAV;

use BaseX\Database;
use BaseX\Collection as BaseXCollection;
use BaseX\Collection\CollectionInfo;
use BaseX\Resource\Raw;
use BaseX\Resource\Document;
use BaseX\Resource\ResourceInfo;

use BaseX\DAV\Resource;
use BaseX\DAV\Collection;

use Sabre_DAV_ObjectTree;
use Sabre_DAV_Exception_NotFound;

/**
 * Description of Tree
 *
 * @author alxarch
 */
class Tree extends Sabre_DAV_ObjectTree
{
  /**
   *
   * @var \BaseX\Session
   */
  protected $session;
  
  /**
   *
   * @var \BaseX\Database
   */
  protected $db;
  
  protected $dir;
  
  /**
   *
   * @var \SimpleXMLElement
   */
  protected $contents;
  
  /**
   *
   * @var array
   */
  protected $excludes = array(
      '*.empty' => null,
      '*.protect*' => null,
  );

  public function __construct(Database $db, $dir=false)
  {
    $this->db = $db;
    $this->session = $db->getSession();
    $this->dir = $dir;
    
    $this->contents = null;
  }
  
  /**
   * 
   * @param string $pattern A glob pattern.
   * @return \Sabre\DAV\BaseX\Tree $this
   */
  public function exclude($pattern)
  {
    $this->excludes[$pattern] = null;
    
    return $this;
  }
  
  /**
   * @return \SimpleXMLElement
   */
  protected function getContents()
  {
    if(null === $this->contents)
    {
      $results = CollectionInfo::get($this->session, $this->db->getName(), '');
      if(empty($results))
      {
        $this->contents = simplexml_load_string('<contents/>');
      }
      else
      {
        $this->contents = $results[0]->getXML()->contents;
      }
    }
    
    return $this->contents;
  }
  
  protected function excluded($path)
  {
    foreach ($this->excludes as $pattern => $_)
    {
      if(fnmatch($pattern, $path))
      {
        return true;
      }
    }
    return false;
  }


  protected function doGetNode(\SimpleXMLElement $xml)
  {
    if($xml->getName() === 'collection')
    {
      $info = new CollectionInfo($this->session);
      $info->setData($xml->asXML());
      
      if($this->excluded($info->getPath()))
      {
        return null;
      }
      
      $col = new BaseXCollection($this->session, $this->db->getName(), $info->getPath(), $info);
      
      return new Collection($this, $col, $this->dir);
    }
    elseif ($xml->getName() === 'resource') 
    {
      $info = new ResourceInfo($this->session);
      $info->setData($xml->asXML());
      
      if($this->excluded($info->getPath()))
      {
        return null;
      }

      if($info->isRaw())
      {
        $res = new Raw($this->session, $this->db->getName(), $info->getPath(), $info);
        $file = false;
        if($this->dir)
        {
          $file = $this->dir . '/' . $info->getPath();
        }

        return new Resource($this, $res, $file);
      }
      else
      {
        $res = new Document($this->session, $this->db->getName(), $info->getPath(), $info);
        return new Resource($this, $res);
      }
    }

    return null;
  }

  public function getChildren($path)
  {
    
    if($this->excluded($path))
    {
      return array();
    }
    
    $xpath = "//collection[@path='$path']/contents";
    $contents = $this->getContents()->xpath($xpath);
    
    if(empty($contents))
    {
      return array();
    }
    
    $children = array();
    
    foreach ($contents[0]->children() as $child)
    {
      $node = $this->doGetNode($child);
      if(null !== $node)
      {
        $children[] = $node;
      }
    }
    
    return $children;
  }

  protected function find($path)
  {
    if($this->excluded($path))
    {
      return null;
    }
    
    $xpath = "//resource[text() = '$path']|//collection[@path = '$path']";
    
    $results = $this->getContents()->xpath($xpath);
    
    if(empty($results) || count($results) > 1)
    {
      return null;
    }
    
    return $this->doGetNode($results[0]);
    
  }
  
  public function getNodeForPath($path)
  {
    
    $node = $this->find($path);
    
    if(null === $node)
    {
      throw new Sabre_DAV_Exception_NotFound('Resource not found');
    }
    
    return $node;
  }

  /**
   * Moves a file from one location to another
   *
   * @param string $sourcePath The path to the file which should be moved
   * @param string $destinationPath The full destination path, so not just the destination parent node
   */
  public function move($sourcePath, $destinationPath) 
  {
    $node = $this->find($sourcePath);
    
    if($node instanceof Resource)
    {
       $node->getResource()->move($destinationPath);
    }
    elseif($node instanceof Collection)
    {
      $node->getCollection()->move($destinationPath);
    }
    else
    {
      throw new \Sabre_DAV_Exception_NotImplemented();
    }
    
    $this->contents = null;
  }
   
  public function delete($path)
  {
    $db = $this->getDatabase()->getName();
    $this->session->query("db:delete('$db', '$path')")->execute();
    $this->contents = null;
  }

  public function copy($sourcePath, $destinationPath) {
    
    $node = $this->find($sourcePath);
    
    if($node instanceof Resource)
    {
       $node->getResource()->copy($destinationPath);
    }
    elseif($node instanceof Collection)
    {
      $node->getCollection()->copy($destinationPath);
    }
    else
    {
      throw new \Sabre_DAV_Exception_NotImplemented();
    }
    
    $this->contents = null;
  }
  
  public function markDirty($path)
  {
    $this->contents = null;
  }
  
  public function nodeExists($path)
  {
    if($this->excluded($path))
    {
      return false;
    }
    
    $xpath = "//resource[text() = '$path']|//collection[@path = '$path']";
    $results = $this->getContents()->xpath($xpath);
    
  
    return $path ? count($results) === 1 : false;
  }
  
  /**
   * 
   * @return string
   */
  public function getDirectory()
  {
    return $this->dir;
  }
  
  /**
   * 
   * @return \BaseX\Database
   */
  public function getDatabase()
  {
    return $this->db;
  }
  
  public function addNode($path, $data=null)
  {
    $this->contents = null;
    
    $xpath = "//resource[@path = '$path']";
    $results = $this->getContents()->xpath($xpath);
    
    $method = 'store';
    if(empty($results))
    {
      $name = basename($path);
      $patterns = explode(',', $this->session->getInfo()->option('createfilter'));
      foreach ($patterns as $p)
      {
        if(fnmatch($p, $name))
        {
          $method = 'add';
        }
      }
    }
    elseif((string)$results[0]['raw'] === 'false')
    {
      $method = 'replace';
    }
    
    $resource = $this->db->{$method}($path, $data);
     
    return sprintf('"%s"', $resource->getEtag());

  }
}
