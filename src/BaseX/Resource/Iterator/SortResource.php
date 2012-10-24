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
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Sorts resource data by a specified key.
 * 
 */
class SortResource implements IteratorAggregate
{

  protected $key;
  protected $iterator;

  public function __construct(Traversable $iterator, $key)
  {
    if (!in_array($key, array('size', 'path', 'modified', 'mime')))
      throw new InvalidArgumentException('Invalid sort key.');

    $this->key = $key;
    $this->iterator = $iterator;
  }

  public function getIterator()
  {

    $data = iterator_to_array($this->iterator);

    $key = $this->key;
    uasort($data, function($a, $b) use ($key) {
        if ($a[$key] === $b[$key])
        {
          return 0;
        }

        return $a[$key] > $b[$key] ? -1 : 1;
      });

    return new ArrayIterator($data);
  }

}