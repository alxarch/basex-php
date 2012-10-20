<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */


namespace BaseX\Query\Results;

use BaseX\Query\Results\ProcessedResults;

/**
 * Simple regex parsing of results
 *
 * @author alxarch
 */
class RegexResults extends ProcessedResults
{
  public $pattern;
  public $matches;

  public function __construct($pattern, $matches=true)
  {
    $this->pattern = $pattern;

    $this->matches = $matches;
  }
  
  protected function processData($data, $type) 
  {
    $matches = array();
    if(preg_match($this->pattern, $data, $matches))
    {
      return $this->matches ? $matches : $data;
    }
    else
    {
      return null;
    }
   
  }

  public function supportsMethod($method) 
  {
    return  true;
  }
}
