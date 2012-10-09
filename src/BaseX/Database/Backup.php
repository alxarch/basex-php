<?php

namespace BaseX\Database;

use BaseX\Query\QueryResult;

class Backup extends QueryResult
{
  protected $db,$size,$file,$date;

  public function setData($data) {
    parent::setData($data);
    $pattern = 
    '/<backup\s+'.
       'size="(?P<size>\d+)">'.
      '(?P<file>'.
        '(?P<db>.+)\-'.
        '(?P<year>\d{4})\-'.
        '(?P<month>\d{2})\-'.
        '(?P<day>\d{2})\-'.
        '(?P<hour>\d{2})\-'.
        '(?P<min>\d{2})\-'.
        '(?P<sec>\d{2})\.zip)'.
     '<\/backup>/';
    $matches = array();
    if(preg_match($pattern, $data, $matches))
    {
      $this->file = $matches['file'];
      $this->size = (int)$matches['size'];
      $this->db = $matches['db'];
      
      $d = sprintf('%d-%d-%d %d:%d:%d', 
              $matches['year'],
              $matches['month'],
              $matches['day'],
              $matches['hour'],
              $matches['min'],
              $matches['sec']);
      
      $this->date = date_parse($d);
      
    }
    else
    {
      throw new \InvalidArgumentException('Invalid backup data.');
    }
    
    return $this;
    
  }
  
  public function getDatabase(){
    return $this->db;
  }
  public function getSize(){
    return $this->size;
  }
  public function getDate(){
    return $this->date;
  }
  public function getFile(){
    return $this->file;
  }
  public function getFilepath(){
    return $this->getSession()->getInfo()->dbpath . DIRECTORY_SEPARATOR . $this->file;
  }
}
