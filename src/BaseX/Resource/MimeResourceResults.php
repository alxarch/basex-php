<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Resource\ResourceResults;
use BaseX\Resource\Raw;

/**
 * Resource results that maps content type to PHP classes.
 *
 * @author alxarch
 */
class MimeResourceResults extends ResourceResults
{

  protected $types;

  public function __construct(\BaseX\Database $db, $types = array())
  {
    parent::__construct($db);
    $this->types = array(
        'application/xml' => 'BaseX\Resource\Document',
    );
    foreach ($types as $mime => $class)
    {
      $this->assignClass($mime, $class);
    }
  }

  public function assignClass($mime, $class)
  {
    if ('application/xml' === $mime)
    {
      if ($class instanceof Document)
      {
        $this->types[$mime] = $class;
      }
      else
      {
        throw new \InvalidArgumentException('Cannot map XML resources to non-Document class.');
      }
    }
    else
    {
      if ($class instanceof Raw)
      {
        $this->types[$mime] = $class;
      }
      else
      {
        throw new \InvalidArgumentException('Cannot map non-XML resource to Document class.');
      }
    }

    return $this;
  }

  protected function processData($data, $type)
  {
    $xml = @simplexml_load_string($data);

    if ($xml instanceof \SimpleXMLElement && isset($xml['content-type']))
    {
      $type = (string) $xml['content-type'];
      if (isset($this->types[$type])
          && ('application/xml' !== $type
          || 'false' === (string) $xml['raw']))
      {
        $class = $this->types[$type];
        return $class::fromSimpleXML($this->db, $xml);
      }
      else
      {
        return Raw::fromSimpleXML($this->db, $xml);
      }
    }
  }

}

