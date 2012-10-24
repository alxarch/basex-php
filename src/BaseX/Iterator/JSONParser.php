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
 * Iterator to parse json data
 *
 * @author alxarch
 */
class JSONParser extends IteratorIterator
{
  public $assoc;
  public $depth;

  public function __construct(Traversable $iterator, $opts=array())
  {
    parent::__construct($iterator);
    
    $opts = array('assoc'=>false, 'depth'=>512) + $opts;
    $this->assoc = $opts['assoc'];
    $this->depth = $opts['depth'];
  }

  public function current()
  {
    $item = parent::current();
    return json_decode($item, $this->assoc, $this->depth);
  }
}
