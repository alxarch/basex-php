<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Tests\Collection;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Collection\CollectionInfo;

/**
 * Description of CollectionInfoTest
 *
 * @author alxarch
 */
class CollectionInfoTest extends TestCaseDb{
  
  function testGetPath()
  {
    $this->db->add('test.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    $this->db->add('test/path/test.xml', '<test/>');
    
    $results = CollectionInfo::get($this->session, $this->dbname, 'test');
    
    $info = $results[0];
    
    $this->assertEquals('test', $info->getPath());
  }
  
  public function testGet()
  {
    $this->db->add('test.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    $this->db->add('test/path/test.xml', '<test/>');
    
    
    $results = CollectionInfo::get($this->session, $this->db);
    
    $this->assertNotEmpty($results);
    $this->assertEquals(1, count($results));
    $this->assertInstanceOf('BaseX\Collection\CollectionInfo', $results[0]);
    $c = $results[0];
    
    $test = $c->xpath('contents/resource');
    $this->assertEquals(1, count($test));
    $this->assertEquals('test.xml', (string)$test[0]);
    
    $test = $c->xpath('contents/collection/@path');
    $this->assertEquals(1, count($test));
    $this->assertEquals('test', (string)$test[0]);
    
    $test = $c->xpath('contents/collection/contents/resource');
    $this->assertEquals(1, count($test));
    $this->assertEquals('test/test.xml', (string)$test[0]);
    
    $test = $c->xpath('contents/collection/contents/collection/@path');
    $this->assertEquals(1, count($test));
    $this->assertEquals('test/path', (string)$test[0]);
    
    $test = $c->xpath('contents/collection/contents/collection/contents/resource');
    $this->assertEquals(1, count($test));
    $this->assertEquals('test/path/test.xml', (string)$test[0]);
 
  }
}
