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
 * Adds documents using the html parser.
 * 
 * @link http://docs.basex.org/wiki/Parsers#HTML_Parser
 * @link http://home.ccil.org/~cowan/XML/tagsoup/#program
 * @link http://docs.basex.org/wiki/Options#CREATEFILTER
 *
 * @author alxarch
 */
class HTML extends Importer
{
  public function getDefaultCreateFilter() {
    return '*.html';
  }
  
  public function getParserOptions() {
    return array(
      'method' => 'xml', 
    );
  }
  
  public function getParser() {
    return 'html';
  }

}

