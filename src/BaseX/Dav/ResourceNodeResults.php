<?php

/**
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Dav;

use BaseX\Dav\ObjectTree;
use BaseX\Dav\ResourceNode;
use BaseX\Resource\ResourceResults;
/**
 * Maps query results to DAV nodes.
 *
 * @package BaseX 
 * @author alxarch
 */
class ResourceNodeResults extends ResourceResults
{
  /**
   *
   * @var \BaseX\Dav\ObjectTree
   */
  protected $tree;
  
  public function __construct(ObjectTree $tree) 
  {
    $this->tree = $tree;
    parent::__construct($tree->getDatabase());
  }
  
  public function processData($data, $type) 
  {
    $res = parent::processData($data, $type);
    
    if(null !== $res)
    {
      $node = new ResourceNode($this->tree);
      $node->mime = $res->getContentType();
      $node->size = method_exists($res, 'getSize') ? $res->getSize() : 0;
      $node->path = $this->tree->getRelativePath($res->getPath());
      $node->resource = $res->isRaw();
      $node->modified = (int) $res->getModified()->format('U');
      return $node;
    }
    
    return null;
  }
}

