<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Iterator;

use DateTime;
use IteratorIterator;
use Traversable;


/**
 * Parses each item in the input iterator as a DateTime object.
 *
 * @author alxarch
 */
class DateTimeParser extends IteratorIterator
{
  /**
   * The date format to use to parse data.
   * 
   * @link http://php.net/manual/en/datetime.createfromformat.php
   * 
   * @var string
   */
  protected $format;
  
  /**
   * Constructor.
   * 
   * @param Traversable $iterator
   * @param string  $format The date format to use to parse data. 
   * If none is specified default DateTime constructor is used
   */
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
