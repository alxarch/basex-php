<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Database;

use BaseX\Error\UnserializationError;
use BaseX\Database;
use BaseX\Query\Result\UnserializableResults;

/**
 * BaseX Database Backup.
 * 
 * @package BaseX 
 */
class Backup
{
  /**
   * The name of the database this backup belongs to.
   * 
   * @var string
   */
  protected $db;
  
  /**
   * Size of the backup in bytes.
   * 
   * @var int
   */
  protected $size;
  
  /**
   * When this backup was taken.
   * 
   * @var \DateTime
   */
  protected $date;
  
  /**
   * The database this backup belongs to.
   * 
   * @return \BaseX\Database
   */
  public function getDatabase(){
    return $this->db;
  }
  
  /**
   * Size of the backup in bytes.
   * 
   * @return int
   */
  public function getSize(){
    return $this->size;
  }
  
  /**
   * When this backup was taken.
   * 
   * @return \DateTime
   */
  public function getDate(){
    return $this->date;
  }
  
  /**
   * Filename for this backup.
   * 
   * @return string
   */
  public function getFile(){
    return sprintf('%s-%s.zip', $this->db, $this->date->format('Y-m-d-H-i-s'));
  }
  
  public function setDate($date)
  {
    $this->date = new \DateTime($date);
  }
  
  /**
   * Location on the disk for this backup.
   * 
   * @return string
   */
  public function getFilepath($dbpath)
  {
    return $dbpath . DIRECTORY_SEPARATOR . $this->getFile();
  }
  
  /**
   * Create a new backup for a Database.
   * 
   * @param \BaseX\Database $db
   * @return \BaseX\Database\Backup
   */
  static public function create(Database $db)
  {
    $db->getSession()->execute("CREATE BACKUP $db");
  }
  
  
  public function unserialize($data) {
     $pattern = 
    '/<backup\s+'.
       'size="(?P<size>\d+)">'.
        '(?P<db>.+)\-'.
        '(?P<year>\d{4})\-'.
        '(?P<month>\d{2})\-'.
        '(?P<day>\d{2})\-'.
        '(?P<hour>\d{2})\-'.
        '(?P<min>\d{2})\-'.
        '(?P<sec>\d{2})\.zip'.
     '<\/backup>/';
    
    $matches = array();
    
    if(preg_match($pattern, $data, $matches))
    {
      $d = sprintf('%d-%d-%d %d:%d:%d', 
              $matches['year'],
              $matches['month'],
              $matches['day'],
              $matches['hour'],
              $matches['min'],
              $matches['sec']);
      
      $this->setDate($d);
      $this->db = $matches['db'];
      $this->size = $matches['size'];
     
    }
    else
    {
      throw new UnserializationError();
    }
  }
  
  public static function getLatestBackup(Database  $db)
  {
    return $this->getBackups($db)->getFirst();
  }
  
  public static function getBackups(Database $db)
  {
    $xql = <<<XQL
      for \$b in db:backups('$this') 
        order by \$b descending
        return \$b
XQL;
    return  $db->getSession()
              ->query($xql)
              ->getResults(new UnserializableResults(get_called_class()));
    
  }
}
