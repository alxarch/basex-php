<?php

/**
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\DAV;

use BaseX\DAV\ObjectTree;
use BaseX\DAV\CollectionNode;
use BaseX\DAV\ResourceNode;
use BaseX\Resource\Interfaces\CollectionInterface;
use BaseX\Resource\Interfaces\StreamableResource;
use BaseX\Resource\ResourceMapper;
use BaseX\Error\ResultMapperError;
use BaseX\Resource\Raw;
use BaseX\Helpers as B;

/**
 * Maps query results to DAV nodes.
 *
 * @package BaseX 
 * @author alxarch
 */
class NodeMapper extends ResourceMapper
{
  /**
   *
   * @var \BaseX\DAV\ObjectTree
   */
  protected $tree;
  
  public function __construct(ObjectTree $tree) 
  {
    parent::__construct($tree->getDatabase());
    $this->tree = $tree;
  }
  
  public function getResultFromXML(\SimpleXMLElement $xml) {
    
    $result = parent::getResultFromXML($xml);
    
    switch (true)
    {
      case $result instanceof CollectionInterface:
        $path =  B::relative($result->getPath(), $this->tree->getRoot());
        $node = new CollectionNode($this->tree, $path);
        $node->modified = (int) $result->getModified()->format('U');
        
        return $node;
        break;
      
      case $result instanceof StreamableResource:
        $path =  B::relative($result->getPath(), $this->tree->getRoot());
        $node = new ResourceNode($this->tree, $path);
        $node->modified = (int) $result->getModified()->format('U');
        $node->mime = $result->getContentType();
        $node->etag = $result->getEtag();
        
        if($result instanceof Raw)
        {
          $node->size = $result->getSize();
        }
        
        return $node;
        break;
      
      default:
        throw new ResultMapperError();
    }
  }
  
}

