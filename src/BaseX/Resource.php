<?php

/**
 * @package BaseX
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX;

use BaseX\Helpers as B;
use BaseX\Resource\ResourceInterface;
use BaseX\Database;

/**
 * Base class for BaseX resources.
 * 
 * @package BaseX
 */
abstract class Resource implements ResourceInterface
{

  const DATE_FORMAT = 'Y-m-d\TH:i:s.u\Z';

  /**
   * The database this resource belongs to.
   * 
   * @var \BaseX\Database
   */
  protected $db;

  /**
   * The path for this resource.
   * 
   * @var string 
   */
  protected $path;

  /**
   * Last modified date.
   * 
   * @var \DateTime 
   */
  protected $modified;
  protected $deleted;

  /**
   * Creates a new resource.
   * 
   * If modified parameter is passed, it is assumed that the resource already
   * exists on the database.
   * 
   * @param \BaseX\Database $db
   * @param string $path
   * @param string $modified
   */
  public function __construct(Database $db, $path)
  {
    $this->db = $db;
    $this->path = (string) $path;
  }

  /**
   * The database this document belongs to.
   * 
   * @return \BaseX\Database
   */
  public function getDatabase()
  {
    return $this->db;
  }

  /**
   * Copy this document to another location.
   * 
   * This will overwrite any documents at that location.
   * 
   * 
   * @param string $dest

   */
  public function copy($dest)
  {
    $this->getDatabase()->copy($this->getPath(), $dest);
  }

  /**
   * Move this to resource to another path.
   * 
   * @param string $dest
   * 
   * @return \BaseX\Resource $this
   */
  public function move($dest)
  {
    $this->getDatabase()->rename($this->getPath(), $dest);
    $this->path = $dest;
    $this->refresh();

    return $this;
  }

  /**
   * Move this to document to another path.
   * 
   * @param string $dest
   * 
   * @return \BaseX\Resource $this
   */
  public function rename($name)
  {
    $from = $this->getPath();
    $to = B::rename($this->getPath(), $name);

    $this->getDatabase()->rename($from, $to);
    $this->path = $to;
    $this->refresh();

    return $this;
  }

  /**
   * Delete this resource.
   */
  public function delete()
  {
    $this->getDatabase()->delete($this->getPath());
    $this->deleted = true;
  }

  /**
   * Reload resource info from the database.
   * 
   */
  abstract public function refresh();

  /**
   * Returns a hash value to be used as an etag.
   * 
   * @return string 
   */
  public function getEtag()
  {
    $etag = sprintf('%s/%s/%d', $this->getDatabase(), $this->getPath(), $this->getModified()->format('Y-m-d\TH:i:s.uP'));

    return md5($etag);
  }

  /**
   * Resource path.
   * 
   * @return string
   */
  public function __toString()
  {
    return (string) $this->path;
  }

  /**
   * Resource path.
   * @return string
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * Resource name only.
   * 
   * @return string
   */
  public function getName()
  {
    return basename($this->getPath());
  }

  /**
   * 
   * @return \DateTime
   */
  public function getModified()
  {
    return $this->modified;
  }

  /**
   * 
   * @return \DateTime
   */
  public function setModified($datetime)
  {
    if ($datetime instanceof \DateTime)
    {
      $this->modified = $datetime;
    }
    else
    {
      $this->modified = new \DateTime($datetime);
    }
  }

  public function exists()
  {
    return !$this->isDeleted() && $this->db->exists($this->getPath());
  }

  public function isDeleted()
  {
    return true === $this->deleted;
  }

  public static function fromSimpleXML(Database $db, \SimpleXMLElement $xml)
  {
    $class = get_called_class();

    $resource = new $class($db, (string) $xml);
    $modified = self::parseDate((string) $xml['modified-date']);
    if (false !== $modified)
    {
      $resource->setModified($modified);
    }

    return $resource;
  }

  /**
   * Converts <resource>'s modified-date attribute to DateTime.
   * 
   * @param string $date
   * 
   * @return \DateTime|null
   */
  static public function parseDate($date)
  {
    $date = date_create_from_format(self::DATE_FORMAT, $date);
    return false === $date ? null : $date;
  }
  
  /**
   *
   * 
   * @param string $line
   * 
   * @return array|null
   */
  static public function parseLine($line)
  {
    $matches = array();
    $pattern = "/(?P<path>.+[^\s])\s+(?P<type>raw|xml)\s+(?P<mime>[^\s]+)\s+(?P<size>\d+)?/";
    if(preg_match($pattern, $line, $matches))
    {
      return $matches;
    }
    
    return null;
  }

}