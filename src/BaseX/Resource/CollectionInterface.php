<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Resource\ResourceInterface;

/**
 * Interface for BaseX Collections.
 *
 * @author alxarch
 */
interface CollectionInterface extends ResourceInterface, \ArrayAccess//, \IteratorAggregate
{
  public function hasChild($name);
  public function getChildren();
  public function getChildPath($name);
  public function getRelativePath($path);
//  public function deleteChild($path);
//  public function moveChild($src, $dest);
//  public function copyChild($src, $dest);
//  public function addChild($name, $data, $method=null);
  
}
