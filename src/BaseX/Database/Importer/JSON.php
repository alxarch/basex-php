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
 * Adds documents using the JSON parser.
 * 
 * @link http://docs.basex.org/wiki/Parsers#JSON_Parser
 * @link http://docs.basex.org/wiki/Options#CREATEFILTER
 *
 * @author alxarch
 */
class JSON extends Importer
{
  public function getDefaultCreateFilter() {
    return '*.json';
  }
  
  public function getParserOptions() {
    return array(
      'encoding' => 'utf-8', 
      'jsonml'   => false
    );
  }
  
  public function getParser() {
    return 'json';
  }

}

