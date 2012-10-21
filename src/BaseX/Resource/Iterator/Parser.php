<?php

namespace BaseX\Resource\Iterator;

use BaseX\Resource;

class Parser extends \IteratorIterator
{
  public function current()
  {
    $line = parent::current();
    
    return Resource::parseLine($line);
  }
}