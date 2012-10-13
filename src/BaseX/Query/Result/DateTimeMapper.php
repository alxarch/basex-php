<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Query\Result;

use BaseX\Query;
use BaseX\Error\ResultMapperError;
use BaseX\Query\Result\MapperInterface;

/**
 * Description of QueryResultFactory
 *
 * @author alxarch
 */
class DateTimeMapper implements MapperInterface
{
  protected $format;
  public function __construct($format = null) {
    $this->format = $format;
  }
  
  public function supportsType($type) {
    switch ($type)
    {
      case Query::TYPE_DATE:
      case Query::TYPE_DATETIME:
      case Query::TYPE_DATETIMESTAMP:
      case Query::TYPE_STRING:
      case Query::TYPE_ATTRIBUTE:
      case Query::TYPE_TEXT:
      case Query::TYPE_INTEGER:
      case Query::TYPE_INT:
      case Query::TYPE_LONG:
      case Query::TYPE_SHORT:
        return true;
        break;
      default:
        return false;
        break;
    }
  }

  /**
   * @todo Check date formats for all types.
   * 
   * @param mixed $data
   * @param int $type
   * @return \DateTime
   * @throws ResultMapperError
   */
  public function getResult($data, $type) {
    $format = $this->format;
    
    if(null === $this->format)
    {
      switch ($type)
      {
        case Query::TYPE_INTEGER:
        case Query::TYPE_INT:
        case Query::TYPE_LONG:
        case Query::TYPE_SHORT:
          $format = 'U';
          break;
        case Query::TYPE_TIME:
          $format = preg_match('/\.\d+$/', $data) ? 'H:i:s.u' : 'H:i:s';
          break;
        default:
          break;
      }
    
    }
 
    try{
      return (null === $format) ? 
        \DateTime::createFromFormat ($format, $data) : 
        new \DateTime($data);
    }
    catch (\Exception $e){
      throw new ResultMapperError();
    }
  }
}

