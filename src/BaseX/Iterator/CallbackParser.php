<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Iterator;

use ArrayIterator;
use InvalidArgumentException;
use IteratorIterator;
use Traversable;

/**
 * Parses each item in the input iterator using a callable.
 * 
 */
class CallbackParser extends IteratorIterator
{

  protected $callback;

  /**
   *
   * @var ArrayIterator
   */
  protected $array;

  public function __construct(Traversable $iter, $callback)
  {
    parent::__construct($iter);

    if (!is_callable($callback))
      throw new InvalidArgumentException('Invalid Callback');

    $this->callback = $callback;
  }

  public function current()
  {
    $item = parent::current();

    if (null === $item)
      return $item;

    return call_user_func($this->callback, $item);
  }

}