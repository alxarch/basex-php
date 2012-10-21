<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Dav\Iterator;

use BaseX\Resource\Iterator\Resources;
use BaseX\Dav\ResourceNode;

/**
 * Description of Nodes
 *
 * @author alxarch
 */
class Nodes extends Resources
{
  /**
   * 
   * @return \ArrayIterator
   */
  public function getIterator()
  {
    $this->setConverter(array($this, 'convertNode'));
    return parent::getIterator();
  }
  
  public function convertNode($resource)
  {
    $node = new ResourceNode($this->db, $resource['path']);
    $node->size = (int)$resource['size'];
    $node->mime = $resource['mime'];
    $node->raw = 'raw' === $resource['type'];
    
    if(null !== $resource['modified'])
      $node->modified = (int)$resource['modified']->format('U');
    
    return $node;
  }
}

