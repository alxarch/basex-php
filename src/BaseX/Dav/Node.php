<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Dav;

use BaseX\Database;
use BaseX\Helpers as B;
use Sabre_DAV_INode;

/**
 * Description of INode
 *
 * @author alxarch
 */
abstract class Node implements Sabre_DAV_INode
{
  /**
   * @var Database
   */
  protected $db;
  public $path;
  public $modified;
  
  public function __construct(Database $db, $path)
  {
    $this->db = $db;
    $this->path = $path;
  }
  
  public function delete() {
    $this->db->delete($this->path);
  }
  
  public function getLastModified() {
    return $this->modified;
  }
  
  public function getName(){
    return basename($this->path);
  }

  public function setName($name){
    $dest = B::rename($this->path, $name);
    $this->db->rename($this->path, $dest);
    $this->path = $dest;
  }
}