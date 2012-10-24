<?php

/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Query;

use BaseX\Iterator\ArrayWrapper;
use BaseX\Iterator\CallbackFilter;
use BaseX\Iterator\CallbackParser;
use BaseX\Iterator\CSVParser;
use BaseX\Iterator\DateTimeParser;
use BaseX\Iterator\GrepFilter;
use BaseX\Iterator\JSONParser;
use BaseX\Iterator\ObjectParser;
use BaseX\Iterator\Reverse;
use BaseX\Iterator\Sort;
use BaseX\Query\QueryResultsInterface;
use Closure;
use SimpleXMLIterator;

/**
 * Iterator for query results
 *
 * @author alxarch
 */
class QueryResults extends ArrayWrapper implements QueryResultsInterface
{

  protected $data;
  protected $types;
  
  protected $parser;
  protected $class;
  protected $csv;
  protected $json;
  protected $format;

  protected function processIterator()
  { 
    $iterator = $this->iterator;
    
    if (null !== $this->grep)
    {
      $iterator = new GrepFilter($iterator, $this->grep);
    }

    if (null !== $this->filter)
    {
      $iterator = new CallbackFilter($iterator, $this->filter);
    }

    switch ($this->parser)
    {
      case 'object':
        $iterator = new ObjectParser($iterator, $this->class);
        break;
      case 'simplexml':
        $iterator = new SimpleXMLIterator($iterator);
        break;
      case 'datetime':
        $iterator = new DateTimeParser($iterator, $this->format);
        break;
      case 'json':
        $iterator = new JSONParser($iterator, $this->json);
        break;
      case 'csv':
        $iterator = new CSVParser($iterator, $this->csv);
        break;
      default:
        if (null !== $this->callback)
          $iterator = new CallbackParser($iterator, $this->callback);
        break;
    }

    if (null !== $this->sort)
    {
      $iterator = new Sort($iterator, $this->sort);
    }

    if (true === $this->reverse)
    {
      $iterator = new Reverse($iterator);
    }

    return $iterator;
  }

  /**
   * 
   * @param string $data
   * @param int $type
   */
  public function addResult($data, $type)
  {
    $this->iterator->append($data);
//    $this->types[] = $type;
    $this->total = null;
    return $this;
  }

  /**
   * 
   * @param string $type
   * @return boolean
   */
  public function supportsType($type)
  {
    return true;
  }

  /**
   * 
   * @param string $method
   * @return boolean
   */
  public function supportsMethod($method)
  {
    return true;
  }

  public function parseJSON($assoc = false, $depth = 512)
  {
    $this->parser = 'json';
    $this->json = array('assoc' => $assoc, 'depth' => $depth);
    return $this;
  }

  public function parseDateTime($format = null)
  {
    $this->parser = 'datetime';
    $this->format = $format;
    return $this;
  }

  public function parseSimpleXML()
  {
    $this->parser = 'simplexml';

    return $this;
  }

  public function parseCSV($header = false, $delimiter = ',', $enclosure = '"',
                           $escape = '\\')
  {
    $this->parser = 'csv';
    $this->csv = array(
      'header'    => (boolean) $header,
      'delimiter' => $delimiter,
      'enclosure' => $enclosure,
      'escape'    => $escape
    );
    return $this;
  }

  public function parseObject($class)
  {
    $this->parser = 'object';
    $this->class = $class;
    return $this;
  }

  public function parseRegex($pattern)
  {
    $this->parser = 'regex';
    $this->pattern = $pattern;
    return $this;
  }

  public function parseCallback(Closure $callback)
  {
    $this->parser = null;
    $this->map($callback);
    return $this;
  }

}
