<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Iterator;

use IteratorIterator;


/**
 * Description of SimpleXML
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
