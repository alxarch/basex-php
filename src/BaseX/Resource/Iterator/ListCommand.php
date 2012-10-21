<?php

namespace BaseX\Resource\Iterator;

use BaseX\Database;

class ListCommand implements \IteratorAggregate
{

  protected $db;
  protected $path;

  public function __construct(Database $db, $path = '')
  {
    $this->db = $db;
    $this->path = $path;
  }

  public function getPath()
  {
    return $this->path;
  }

  public function getDatabase()
  {
    return $this->db;
  }

  public function getIterator()
  {
    $data = $this->db
      ->getSession()
      ->execute("LIST $this->db \"$this->path\"");

    $lines = explode("\n", $data);

    array_shift($lines);
    array_shift($lines);
    array_pop($lines);
    array_pop($lines);
    array_pop($lines);
    
    return new \ArrayIterator($lines);
    
  }

}