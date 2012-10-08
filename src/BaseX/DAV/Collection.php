<?php
/**
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\DAV;

use BaseX\Database;
use BaseX\Collection\CollectionInterface;
use BaseX\Resource\ResourceInterface;

use BaseX\DAV\Tree;

use Sabre_DAV_Collection;
use Sabre_DAV_Exception_NotFound;
use Sabre_DAV_Exception_NotImplemented;

/**
 * WebDAV collection node representing a collection in a BaseX database.
 * 
 * @package SabreDAV-BaseX
 * 
 */
class Collection extends Sabre_DAV_Collection
{
  /**
   *
   * @var \BaseX\Collection
   */
  protected $collection;
  
  /**
   * @var \Sabre\DAV\BaseX\Tree
   */
  protected $tree;
  
  /**
   * Constructor
   * 
   * @param Database $db
   * @param string $path
   * @param string $dir A directory corresponding to the location of this 
   * collection in the filesystem
   */
  public function __construct(Tree $tree, CollectionInterface $collection)
  {
    $this->collection = $collection;
    $this->tree = $tree;
  }
  
  public function getChildren()
  {
    return $this->tree->getChildren($this->collection->getPath());
  }
  
  public function getName()
  {
    return $this->collection->getName();
  }
  
  public function getPath($name=null)
  {
    $path = $this->collection->getPath();
    if(!$path)
    {
      return (string)$name;
    }
    if($name)
    {
      return "$path/$name";
    }
    else
    {
      return $path;
    }
  }

  /**
   * Returns a child object, by its name.
   *
   * This method makes use of the getChildren method to grab all the child nodes, and compares the name.
   * Generally its wise to override this, as this can usually be optimized
   *
   * @param string $name
   * @throws Sabre_DAV_Exception_NotFound
   * @return Sabre_DAV_INode
   */
  public function getChild($name) 
  {
    return $this->tree->getNodeForPath($this->getPath($name));
  }
  
  /**
   * Checks is a child-node exists.
   *
   * It is generally a good idea to try and override this. Usually it can be optimized.
   *
   * @param string $name
   * @return bool
   */
  public function childExists($name) 
  {
    return $this->tree->nodeExists($this->getPath($name));
  }

  /**
   * Creates a new file in the directory
   *
   * Data will either be supplied as a stream resource, or in certain cases
   * as a string. Keep in mind that you may have to support either.
   *
   * After succesful creation of the file, you may choose to return the ETag
   * of the new file here.
   *
   * The returned ETag must be surrounded by double-quotes (The quotes should
   * be part of the actual string).
   *
   * If you cannot accurately determine the ETag, you should not return it.
   * If you don't store the file exactly as-is (you're transforming it
   * somehow) you should also not return an ETag.
   *
   * This means that if a subsequent GET to this new file does not exactly
   * return the same contents of what was submitted here, you are strongly
   * recommended to omit the ETag.
   *
   * @param string $name Name of the file
   * @param resource|string $data Initial payload
   * @return null|string
   */
  public function createFile($name, $data = null) 
  {
    $path = $this->getPath($name);
    
    return $this->tree->addNode($path, $data);
  }

  /**
   * Creates a new subdirectory
   *
   * In order to achieve this an empty file with name '.empty' is created
   * 
   * @param string $name
   * @return void
   */
  public function createDirectory($name) 
  {
    $this->collection->addChild($name.'/.empty', '', true);
    $this->collection->reloadInfo();
  }

  /**
   * Returns the last modification time
   *
   * @return int
   */
  public function getLastModified() 
  {
    return strtotime($this->collection->getModified());
  }

  /**
   * Deleted the current node
   *
   * @throws Sabre_DAV_Exception_NotImplemented
   * @return void
   */
  public function delete() 
  {
    $this->collection->delete();
  }

  /**
   * Renames the node
   *
   * @throws Sabre_DAV_Exception_NotImplemented
   * @param string $name The new name
   * @return void
   */
  public function setName($name) 
  {
    $this->collection->rename($name);
  }
  
  protected function excludeResource(ResourceInterface $resource)
  {
    return false;
  }
  
  public function getFullPath()
  {
    return $this->collection->getPath();
  }
  
  public function getCollection()
  {
    return $this->collection;
  }
  
}
