<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Query\Results;


use BaseX\Query\Results\ProcessedResults;

/**
 * Description of DateTimeQueryResults
 *
 * @author alxarch
 */
class DateTimeResults extends ProcessedResults
{
  public $format;
  
  public function __construct($format=null) {
    $this->format = $format;
  }
  
  public function setFormat($format)
  {
    $this->format = $format;
  }
  
  protected function processData($data, $type)
  {
    if(null !== $this->format)
    {
      $result = \DateTime::createFromFormat($this->format, $data);
    }
    else
    {
      $result = date_create($data);
    }

    return false === $result ? null : $result;
  }

  public function supportsMethod($method) {
    return 'xml' === $method || 'text' === $method;
  }
  
}
