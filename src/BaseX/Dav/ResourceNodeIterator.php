<?php

/**
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Dav;

use BaseX\Resource\Iterator\ResourceIterator;
use BaseX\Dav\ObjectTree;
use BaseX\Dav\ResourceNode;

/**
 * Maps query results to DAV nodes.
 *
 * @package BaseX 
 * @author alxarch
 */
class ResourceNodeIterator extends ResourceIterator
{
  /**
   *
   * @var \BaseX\Dav\ObjectTree
   */
  protected $tree;
  
  public function __construct(ObjectTree $tree, $path=null) 
  {
    
    $this->tree = $tree;
    parent::__construct($tree->getDatabase(), $tree->getFullpath($path), true);
  }

  protected function asObject($matches)
  {
    $node = new ResourceNode($this->tree, $this->tree->getRelativePath($matches['path']));
    $node->size = (int)$matches['size'];
    $node->mime = $matches['content_type'];
    
    if(null !== $matches['modified'])
      $node->modified = (int)$matches['modified']->format('U');
    
    return $node;
  }
  
}

