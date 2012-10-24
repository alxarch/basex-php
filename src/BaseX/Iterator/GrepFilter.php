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


/**
 * Filter an iterator using a regular expression.
 *
 * @author alxarch
 */
class GrepFilter extends FilterIterator
{
  protected $pattern;
  
  public function __construct(\Iterator $iterator, $pattern)
  {
    $this->pattern = $pattern;
    parent::__construct($iterator);
  }
  
  public function accept()
  {
    return preg_match($this->pattern, parent::current());
  }
}
