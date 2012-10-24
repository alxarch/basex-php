<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Iterator;

use IteratorIterator;
use Traversable;


/**
 * Description of RegexParser
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
    if(preg_match($this->pattern, $item, $groups))
    {
      return  $groups;
    }
    else
    {
      return null;
    }
  }
}

