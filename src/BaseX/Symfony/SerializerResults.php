<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Symfony;

use BaseX\Query\Results\ProcessedResults;
use BaseX\Symfony\SerializerResults;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Description of SerializerQueryResults
 *
 * @author alxarch
 */
class SerializerResults extends ProcessedResults
{
  /**
   *
   * @var SerializerInterface
   */
  protected $serializer;

  protected $format;
  protected $type;

  public function __construct(SerializerInterface $serializer, $type=null, $format=null) 
  {
    $this->serializer = $serializer;
    $this->format = $format;
    $this->type = $type;
  }
  
  /**
   * 
   * @param string $type
   * @return SerializerResults
   */
  public function setType($type)
  {
    $this->type = (string) $type;
    return $this;
  }

  /**
   * 
   * @param string $format
   * @return SerializerResults
   */
  public function setFormat($format)
  {
    $this->format = (string)$format; 
    return $this;
  }

  /**
   * 
   * @param SerializerInterface $serializer
   * @return SerializerResults
   */
  public function setSerializer(SerializerInterface $serializer) {
    $this->serializer = $serializer;
    return $this;
  }

  /**
   * 
   * @param string $type
   * @param string $format
   * @return array[]mixed
   */
  public function processData($data, $type)
  {
    return $this->serializer->deserialize($data, $this->type, $this->format);
  }
}
