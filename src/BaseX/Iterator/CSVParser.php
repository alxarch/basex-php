<?php

/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Iterator;

use IteratorIterator;
use Traversable;

/**
 * Parses values in the input iterator as csv.
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
   * Constructor
   * 
   * @see str_get_csv
   * 
   * @param Traversable $iterator
   * @param boolean $header Whether to treat the first row as header and 
   * use it's values as keys for the rest.
   * @param array $options Options to pass to str_get_csv delimiter, enclosure, 
   * escape
   */
  public function __construct(Traversable $iterator, $header=false, $options = array())
  {
    parent::__construct($iterator);

    $opts = array(
      'delimiter' => ',',
      'enclosure' => '"',
      'escape'    => '\\'
      ) + $options;

    $this->delimiter = (string)$opts['delimiter'];
    $this->header = $header;
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

  /**
   * Gets current row.
   * 
   * If header was set the array will be an associative array using first row
   * values as keys.
   * 
   * @return array
   */
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

