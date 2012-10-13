<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource\Interfaces;

use BaseX\Resource\Tree;
use BaseX\Resource\Interfaces\ResourceInterface;
use BaseX\Query\Result\SimpleXMLMapperInterface;
use BaseX\Query\Result\MapperInterface;

/**
 * Interface for BaseX Collections.
 *
 * @author alxarch
 */
interface CollectionInterface extends ResourceInterface
{
  public function getResources($path=null, MapperInterface $mapper = null);
  public function hasChild($name);
//  public function addChild($name, $data);
  public function getChildren(SimpleXMLMapperInterface $mapper = null);
  public function rename($name);
  public function setTree(Tree $tree);
}
