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
 * QueryResults for csv data
 *
 * @author alxarch
 */
class CSVResults extends ProcessedResults
{
  public $delimiter;
  public $enclosure;
  public $escape;
  public $header;

  protected $keys;

  /**
   * 
   * @param boolean $header
   * @param string $delimiter
   * @param string $enclosure
   * @param string $escape
   */
  public function __construct($header=false, $delimiter=',', $enclosure='"', $escape='\\')
  {
    $this->delimiter = $delimiter;
    $this->header = $header;
    $this->escape = $escape;
    $this->enclosure = $enclosure;
  }

  public function setData($data) {
    if($this->header)
    {
      $keys = array_shift($data);
      $this->keys = str_getcsv($keys, $this->delimiter, $this->enclosure, $this->escape);
    }
    
    parent::setData($data);
    
    return $this;
  }
  
  protected function processData($data, $type)
  {
    $values = str_getcsv($data, $this->delimiter, $this->enclosure, $this->escape);
    
    if($this->header)
    {
      return array_combine($this->keys, $values);
    }
    
    return $values;
  }

  public function supportsMethod($method) {
    return 'xml' === $method || 'text' === $method;
  }
}