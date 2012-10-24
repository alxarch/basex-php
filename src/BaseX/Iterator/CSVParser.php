<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Iterator;

use IteratorIterator;
use Traversable;

/**
 * Description of CSVIterator
 *
 * @author alxarch
 */
class CSVParser extends IteratorIterator
{

  protected $delimiter;
  protected $enclosure;
  protected $escape;
  protected $header;
  protected $keys;

  /**
   * 
   * @param Traversable $iterator
   * @param array $options header, delimiter, enclosure, escape
   */
  public function __construct(Traversable $iterator, $options = array())
  {
    parent::__construct($iterator);

    $opts = array(
      'header'    => false,
      'delimiter' => ',',
      'enclosure' => '"',
      'escape'    => '\\'
      ) + $options;

    $this->delimiter = (string)$opts['delimiter'];
    $this->header = (boolean)$opts['header'];
    $this->escape = (string)$opts['escape'];
    $this->enclosure = (string)$opts['enclosure'];
  }

  public function rewind()
  {
    parent::rewind();

    // If first row is header we must parse it to get keys.
    if ($this->header)
    {
      $keys = parent::current();
      parent::next();
      $this->keys = str_getcsv($keys, $this->delimiter, $this->enclosure, $this->escape);
    }
  }

  public function current()
  {

    $data = parent::current();

    $values = str_getcsv($data, $this->delimiter, $this->enclosure, $this->escape);

    if ($this->header)
    {
      return array_combine($this->keys, $values);
    }

    return $values;
  }

}

