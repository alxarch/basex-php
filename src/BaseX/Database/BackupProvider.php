<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Database;

use BaseX\Query\Result\MapperInterface;
use \IteratorAggregate;
use BaseX\Query;
use BaseX\Database;

/**
 * Maps query result data to Backup class.
 *
 * @author alxarch
 */
class BackupProvider implements MapperInterface, IteratorAggregate
{
  /**
   *
   * @var \BaseX\Database
   */
  protected $db;
  
  public function __construct(Database $db) {
    $this->db = $db;
  }
  
  public function supportsType($type) {
     return $type === Query::TYPE_NODE || $type === Query::TYPE_ELEMENT;
  }
  
  public function getResult($data, $type) {
    $backup = new Backup();
    $backup->unserialize($data);
    return $backup;
  }

  
  
  public function getIterator() {
    return $this->getBackups();
  }
}

