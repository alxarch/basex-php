<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Iterator;

use IteratorIterator;
use Traversable;

/**
 * Parses elements in the input iterator using a regular expression.
 *
 * @author alxarch
 */
class RegexParser extends IteratorIterator
{

  protected $pattern;

  public function __construct(Traversable $iterator, $pattern)
  {
    parent::__construct($iterator);

    $this->pattern = $pattern;
  }

  public function current()
  {
    $item = parent::current();

    $groups = array();
    if (preg_match($this->pattern, $item, $groups))
    {
      return $groups;
    }
    else
    {
      return null;
    }
  }

}

