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
  
  protected $filters;
  
  public function addFilter($pattern, $type=self::FILTER_REGEX)
  {
    if (!isset($this->filters[$pattern]) || $this->filters[$pattern] !== $type)
    {
      $this->filters[$pattern] = $type;
      $this->idx = null;
    }

    return $this;
  }
  
  public function accept()
  {
    $resource = parent::current();
    
    $path = $resource['path'];
    
    $skip = false;
    foreach ($this->filters as $pattern => $type)
    {
      switch ($type)
      {
        case self::FILTER_REGEX:
        default:
          $skip = preg_match($pattern, $path);
          break;
        
        case self::FILTER_NAME_REGEX:
          $skip = preg_match($pattern, basename($path));
          break;
        case self::FILTER_NAME_GLOB:
          $skip = fnmatch($pattern, basename($path));
          break;
        
        case self::FILTER_GLOB:
          $skip = fnmatch($pattern, $path);
          break;
      }

      if ($skip) return false;
    }
    
    return true;
  }
}