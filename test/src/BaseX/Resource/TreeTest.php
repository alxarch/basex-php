<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Resource;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Tree;
/**
 * Description of TreeTest
 *
 * @author alxarch
 */
class TreeTest extends TestCaseDb{
  public function testData()
  {
    $this->db->add('test.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    $this->db->add('test/path/test.xml', '<test/>');
    
    $data = Tree::getTreeData($this->db);
    
    $this->assertEquals('', $data);
  }
}

