<?php

/**
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Dav;

use BaseX\Resource\Iterator\ResourceIterator;
use BaseX\Dav\ResourceNode;

/**
 * Maps query results to DAV nodes.
 *
 * @package BaseX 
 * @author alxarch
 */
class ResourceNodeIterator extends ResourceIterator
{
  protected function getObject($matches)
  {
    $node = new ResourceNode($this->db, $matches['path']);
    $node->size = (int)$matches['size'];
    $node->mime = $matches['content_type'];
    $node->raw = 'raw' === $matches['type'];
    
    if(null !== $matches['modified'])
      $node->modified = (int)$matches['modified']->format('U');
    
    return $node;
  }
  
}

