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
  
  
  public function setUp()
  {
    parent::setUp();
    $this->db->add('test.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    $this->db->add('sa/test.xml', '<test/>');
    $this->db->add('test/path/test.xml', '<test/>');
    
  }
  
  public function loadItems($path)
  {
    $items = array();
    foreach ($this->db->getResources($path) as $r)
    {
       $items[$r->getPath()] = $r;
    }
    return $items;
  }
  
  public function testItemLoaderCallable()
  {  
    $tree = new Tree('');
    $tree->setItemLoader(array($this, 'loadItems'));
    
    $result = $tree->rebuild()->offsetGet('');
    
    $this->assertTrue($result instanceof Tree);
    $this->assertEquals(3, count($result->getChildren()));
  }
  
  public function testTreeConverter()
  {  
    $tree = new Tree('');
    $tree->setItemLoader(array($this, 'loadItems'));
    $result = $tree->rebuild()->offsetGet('');
    $this->assertTrue($result instanceof Tree);
  }
}

