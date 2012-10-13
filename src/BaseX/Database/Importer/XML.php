<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Database\Importer;

use BaseX\Database\Importer;

/**
 * Imports resources using the xml parser.
 * 
 * @link http://docs.basex.org/wiki/Parsers#XML_Parser
 * 
 * @author alxarch
 */
class XML extends Importer
{
  public function getDefaultCreateFilter() {
    return '*.xml';
  }
  
  public function getParser() {
    return 'xml';
  }
  
  public function getParserOptions() {
    return array();
  }
  
}

