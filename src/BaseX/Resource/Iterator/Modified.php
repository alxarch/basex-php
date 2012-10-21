<?php

namespace BaseX\Resource\Iterator;

use BaseX\Resource;
use BaseX\Database;
use BaseX\Query\QueryBuilder;
use BaseX\Query\Results\DateTimeResults;

class Modified extends \IteratorIterator
{
  protected $timestamps;

  public function __construct(\Traversable $iterator, Database $db, $path)
  {
    parent::__construct($iterator);
    $this->getTimestamps($db, $path);
  }
  
  private function getTimestamps($db, $path)
  {
    
    $xql = sprintf("db:list-details('%s', '%s')/@modified-date/string()", $db, $path);
    $this->timestamps = QueryBuilder::begin()
      ->setBody($xql)
      ->getQuery($db->getSession())
      ->getResults(new DateTimeResults(Resource::DATE_FORMAT));
  }

  public function current()
  {
    $resource = $this->getInnerIterator()->current();
    $resource['modified'] = $this->timestamps[$this->getInnerIterator()->key()];
    return $resource;
  }
}