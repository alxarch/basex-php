<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Iterator;

use FilterIterator;


/**
 * Description of GrepFilter
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
