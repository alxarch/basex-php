<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Iterator;

use FilterIterator;
use InvalidArgumentException;
use Traversable;
use FilterIterator;

/**
 * CallbackFilterIterator for php 5.3
 *
 * @author alxarch
 */
class CallbackFilter extends FilterIterator
{

  protected $callback;

  public function __construct(Traversable $iter, $callback)
  {
    parent::__construct($iter);

    if (!is_callable($callback))
      throw new InvalidArgumentException('Invalid Callback');

    $this->callback = $callback;
  }

  public function accept()
  {
    $resource = parent::current();
    return call_user_func($this->callback, $resource);
  }

}
