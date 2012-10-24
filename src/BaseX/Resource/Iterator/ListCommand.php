<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource\Iterator;

use ArrayIterator;
use BaseX\Database;
use BaseX\Iterator\CallbackParser;
use IteratorAggregate;

/**
 * Fetches the results of a LIST command to an array of arrays.
 */
class ListCommand implements IteratorAggregate
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

    $resources = new ArrayIterator($lines);
    return new CallbackParser($resources, array('\BaseX\Resource', 'parseLine'));
  }

}