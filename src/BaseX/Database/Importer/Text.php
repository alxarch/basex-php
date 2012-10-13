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
 * Adds documents using the Text parser.
 * 
 * @link http://docs.basex.org/wiki/Parsers#Text_Parser
 * @link http://docs.basex.org/wiki/Options#CREATEFILTER
 *
 * @author alxarch
 */
class Text extends Importer
{
  public function getDefaultCreateFilter() {
    return '*.txt,*.log';
  }
  
  public function getParserOptions() {
    return array(
      'encoding' => 'utf-8', 
      'lines'    => true
    );
  }
  
  public function getParser() {
    return 'text';
  }

}

