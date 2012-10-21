<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource\Iterator;

/**
 * Excludes resourcess by path based on patterns.
 * 
 */
class Exclude extends \FilterIterator
{

  const FILTER_GLOB = 1;
  const FILTER_REGEX = 2;
  const FILTER_NAME_GLOB = 4;
  const FILTER_NAME_REGEX = 8;

  protected $filters = array();

  public function addFilter($pattern, $type = self::FILTER_REGEX)
  {
    $this->filters[$pattern] = $type;

    return $this;
  }

  public function accept()
  {
    $resource = parent::current();

    $path = $resource['path'];

    foreach ($this->filters as $pattern => $type)
    {
      switch ($type)
      {
        case self::FILTER_REGEX:
        default:
          if (preg_match($pattern, $path))
            return false;
          break;

        case self::FILTER_NAME_REGEX:
          if (preg_match($pattern, basename($path)))
            return false;
          break;

        case self::FILTER_NAME_GLOB:
          if (fnmatch($pattern, basename($path)))
            return false;
          break;

        case self::FILTER_GLOB:
          if (fnmatch($pattern, $path))
            return false;
          break;
      }
    }

    return true;
  }

}