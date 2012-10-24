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

/**
 * Converts elements in the input iterator to SimpleXML objects.
 *
 * @author alxarch
 */
class SimpleXMLParser extends IteratorIterator
{

  public function current()
  {
    $data = parent::current();
    return @simplexml_load_string($data);
  }

}
