<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Query\Result;

use SimpleXMLElement;


/**
 * Allows for results to derive directly from simplexml data.
 * 
 * @author alxarch
 */
interface SimpleXMLMapperInterface
{
  /**
   * 
   * @param \SimpleXMLElement $xml
   * @return mixed
   */
  public function getResultFromXML(SimpleXMLElement $xml);

}
