<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Collection;

use BaseX\Resource\ResourceInterface;

/**
 * Interface for BaseX Collections.
 *
 * @author alxarch
 */
interface CollectionInterface extends ResourceInterface
{
  public function listContents();
  public function getResources($path=null);
  public function hasChild($name);
  public function addChild($name, $data, $raw=false);
  public function rename($name);
}
