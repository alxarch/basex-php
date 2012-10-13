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
 * Adds documents using the CSV parser.
 * 
 * @link http://docs.basex.org/wiki/Parsers#CSV_Parser
 * @link http://docs.basex.org/wiki/Options#CREATEFILTER
 *
 * @author alxarch
 */
class CSV extends Importer
{
  public function getDefaultCreateFilter() {
    return '*.csv';
  }
  
  public function getParserOptions() {
    return array(
      'encoding'  => 'utf-8', 
      'separator' => 'comma', 
      'format'    => 'simple',
      'header'    => true
    );
  }
  
  public function getParser() {
    return 'csv';
  }

}
