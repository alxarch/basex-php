<?php

namespace BaseX\Resource\Iterator;

use BaseX\Database;
use BaseX\Query\QueryBuilder;
use BaseX\Resource;
use IteratorIterator;
use Traversable;

class Modified extends IteratorIterator
{
  protected $timestamps;

  public function __construct(Traversable $iterator, Database $db, $path)
  {
    parent::__construct($iterator);
    $this->getTimestamps($db, $path);
  }
  
  private function getTimestamps($db, $path)
  {
    $xql = sprintf("db:list-details('%s', '%s')/@modified-date/string()", $db, $path);
    $timestamps = QueryBuilder::begin()
      ->setBody($xql)
      ->getQuery($db->getSession())
      ->execute();
    $this->timestamps = explode(' ', $timestamps);
  }

  public function current()
  {
    $resource = $this->getInnerIterator()->current();
    $timestamp = $this->timestamps[$this->getInnerIterator()->key()];
    $resource['modified'] = Resource::parseDate($timestamp);
    return $resource;
  }
}