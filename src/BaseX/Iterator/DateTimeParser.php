<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Iterator;

use DateTime;
use IteratorIterator;
use Traversable;


/**
 * Description of DateTime
 *
 * @author alxarch
 */
class DateTimeParser extends IteratorIterator
{
  protected $format;
  
  public function __construct(Traversable $iterator, $format=null) {
    $this->format = $format;
    parent::__construct($iterator);
  }
  
  public function current()
  {
    $data = parent::current();
    
    if(null !== $this->format)
    {
      $result = DateTime::createFromFormat($this->format, $data);
    }
    else
    {
      $result = date_create($data);
    }

    return false === $result ? null : $result;
  }
  
}
