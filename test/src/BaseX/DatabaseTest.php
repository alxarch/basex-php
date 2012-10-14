<?php

namespace BaseX;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Database;
use BaseX\Resource\ResourceMapper;

class DatabaseTest extends TestCaseDb 
{

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Invalid database name.
   */
  public function test__construct() {

    $name = 'not ok όνομα';
    $db = new Database($this->session, $name);
  }

  public function testInit() {
//    $this->assertContains($this->dbname, $this->session->execute("LIST"));
  }

  public function testGetName() {
    $this->assertEquals($this->dbname, $this->db->getName());
  }
  

  /**
   * @depends testInit 
   */
  public function testAdd() {
    $path = 'test.xml';
    $input = '<test>This is a test.</test>';

    $this->db->add($path, $input);

    $this->assertContains($path, $this->ls());

    $this->assertXmlStringEqualsXmlString($input, $this->doc($path));

    return $path;
  }

  /**
   * @depends testInit 
   */
  public function testStore() {
    $path = 'test.txt';
    $input = 'This is a test.';

    $this->db->store($path, $input);

    $this->assertContains($path, $this->ls());

    $contents = $this->raw($path);

    $this->assertEquals($input, $contents);

    return $path;
  }

  /**
   * @depends testAdd 
   * @depends testStore
   */
  public function testDelete($doc, $raw) {

    $this->db->delete($doc);

    $this->assertNotContains($doc, $this->ls());

    $this->db->delete($raw);

    $this->assertNotContains($raw, $this->ls());
  }

  /**
   * @depends testDelete
   */
  public function testRename() {
    $old = 'old.xml';
    $new = 'new.xml';

    $input = '<test>This is a test.</test>';

    $this->db->add($old, $input);

    $this->db->rename($old, $new);
    $this->assertNotContains($old, $this->ls());
    $this->assertContains($new, $this->ls());

    $contents = $this->doc($new);

    $this->assertXmlStringEqualsXmlString($input, $contents);

    $this->db->delete($new);
  }

  /**
   * @depends testDelete
   */
  public function testReplace() {
    $path = "test.xml";
    $old = "<old/>";
    $new = "<new/>";

    $this->db->add($path, $old);
    $this->db->replace($path, $new);

    $this->assertXmlStringEqualsXmlString($new, $this->doc($path));

    $this->db->delete($path);

    $path = 'test.txt';
    $old = "old";
    $new = "new";

    $this->db->store($path, $old);
    $this->db->replace($path, $new);

    $this->assertEquals($new, $this->raw($path));

    $this->db->delete($path);
  }

  public function testExecute() {

    // Open another database.
    $this->session->execute('CHECK other');
    $this->session->execute('OPEN other');

    $info = $this->db->execute('INFO DB');

    $this->assertContains("Name: " . $this->dbname, $info);

    $this->session->execute('DROP DB other');
  }

  public function testGetResource() {
    $this->db->add('test-1.xml', '<test1/>');
    $this->db->add('dir/test-2.xml', '<test2/>');
    $this->db->add('dir/test-3.xml', '<test3/>');
    $this->db->store('test.txt', 'test');

    $resource = $this->db->getResource('test-1.xml');

    $this->assertNotNull($resource);
    $this->assertInstanceOf('BaseX\Resource', $resource);
    $this->assertEquals('test-1.xml', $resource->getPath());
  }

  /**
   * @depends testDelete
   */
  public function testGetResources() {
    $this->db->add('test-1.xml', '<test1/>');
    $this->db->add('dir/test-2.xml', '<test2/>');
    $this->db->add('dir/test-3.xml', '<test3/>');
    $this->db->store('test.txt', 'test');

    $provider = new ResourceMapper($this->db);

    $resources = $this->db->getResources('');

    $this->assertTrue(is_array($resources));
    $this->assertEquals(4, count($resources));

    foreach ($resources as $r) {
      $this->assertInstanceOf('BaseX\Resource', $r);
    }

    $resource = $resources[0];
    $this->assertEquals('test-1.xml', $resource->getPath());
    $resource = $resources[1];
    $this->assertEquals('dir/test-2.xml', $resource->getPath());
    $resource = $resources[2];
    $this->assertEquals('dir/test-3.xml', $resource->getPath());
    $resource = $resources[3];
    $this->assertEquals('test.txt', $resource->getPath());

    $resources = $this->db->getResources('dir/', $provider);
    $this->assertTrue(is_array($resources));
    $this->assertEquals(2, count($resources));

    foreach ($resources as $r) {
      $this->assertInstanceOf('BaseX\Resource', $r);
    }

    $this->db->delete('test-1.xml');
    $this->db->delete('test-2.xml');
    $this->db->delete('test-3.xml');
    $this->db->delete('test.txt');
  }
  
  public function testCreate()
  {
    $name = 'dsfasdaf';
    $db = new Database($this->session, $name);
    $db->create();
    $this->assertContains($name, $this->session->execute('LIST'));
  }
  
  public function testCopy()
  {
    $this->db->add('test/test.xml', '<test/>');
    $this->db->store('test/test.txt', 'test');
    
    $this->db->copy('test/test.xml', 'sa/test.xml');
    
    $this->assertContains('sa/test.xml', $this->ls());
    $this->assertXmlStringEqualsXmlString('<test/>', $this->doc('sa/test.xml'));
    
    $this->db->copy('test', 'sazam');
    $this->assertContains('test/test.xml', $this->ls());
    $this->assertContains('test/test.txt', $this->ls());
    $this->assertContains('sazam/test.xml', $this->ls());
    $this->assertContains('sazam/test.txt', $this->ls());
    
    $this->assertEquals('test', $this->raw('sazam/test.txt'));
    
  }
  
  public function testExists()
  {
    $this->assertFalse($this->db->exists('test.xml'));
    $this->db->add('test/test.xml', '<test/>');
    $this->assertFalse($this->db->exists('test.xml'));
    $this->assertTrue($this->db->exists('test/test.xml'));
    $this->assertTrue($this->db->exists('test'));
  }
  
  public function testGetResourceMapper()
  {
    $this->assertInstanceOf('BaseX\Resource\ResourceMapper', $this->db->getResourceMapper());
    
    $m = new Query\Result\SimpleXMLMapper();
    $this->db->setResourceMapper($m);
    $this->assertEquals($m, $this->db->getResourceMapper());
  }
  

//  /**
//   * @depends testDelete
//   */
//  public function testAddXML()
//  {
//    $input = '<root/>';
//    $path = 'test.xml';
//    
//    $this->db->addXML($path, $input);
//    
//    $this->assertContains($path, $this->ls());
//    $actual = $this->doc($path);
//    $this->assertXmlStringEqualsXmlString($input, $actual);
//    
//    $this->db->delete($path);
//    
//    $this->db->addXML(array($path => $input), null);
//    $this->assertContains($path, $this->ls());
//    $actual = $this->doc($path);
//    $this->assertXmlStringEqualsXmlString($input, $actual);
//    
//    $this->assertResetAfterAdd();
//    
//    $this->db->delete($path);
//  }
//  
//  /**
//   * @depends testDelete
//   */
//  public function testAddHTML()
//  {
//    $html = <<<HTML
//    <!doctype html>
//    <html>
//      <head>
//        <meta charset="utf-8">
//        <title>Hello</title>
//      </head>
//      <body>
//        <h1>Hello Test!</h1>
//      </body>
//    </html>
//HTML;
//    
//    $path = "test.html";
//    
//    $this->db->addHTML($path, $html);
//    
//    $this->assertContains($path, $this->ls());
//    
//    $this->assertResetAfterAdd();
//    
//    $this->db->delete($path);
//  }
//  
//  /**
//   * @depends testDelete
//   */
//  public function testAddJSON()
//  {
//    $json = '{"key": "value"}';
//    
//    $path = "test.json";
//    
//    $this->db->addJSON($path, $json);
//    
//    $this->assertContains($path, $this->ls());
//    
//    $this->assertResetAfterAdd();
//  }
//  
//  /**
//   * @depends testDelete
//   */
//  public function testAddCSV()
//  {
//    $json = "val1, val2\nval1, val2";
//    
//    $path = "test.csv";
//    
//    $this->db->addCSV($path, $json);
//    
//    $this->assertContains($path, $this->ls());
//    
//    $this->assertResetAfterAdd();
//    
//    $this->db->delete($path);
//  }

  protected function assertResetAfterAdd() {
//    $actual = $this->session->execute('GET PARSER');
//    $this->assertEquals("PARSER: xml\n", $actual);
//    
//    $actual = $this->session->execute('GET PARSEROPT');
//    $this->assertEquals("PARSEROPT: \n", $actual);
//    
//    $actual = $this->session->execute('GET HTMLOPT');
//    $this->assertEquals("HTMLOPT: \n", $actual);
//    
//    $actual = $this->session->execute('GET CREATEFILTER');
//    $this->assertEquals("CREATEFILTER: \n", $actual);
  }

//  /**
//   * @depends testDelete
//   */
//  public function testExists()
//  {
//    $this->assertFalse($this->db->exists('file_'.time().'.xml'));
//    $this->db->add('test.xml', '<test/>');
//    $this->assertTrue($this->db->exists('test.xml'));
//    $this->db->delete('test.xml');
//  }

  /**
   * @depends testDelete 
   */
  public function testXpath() {
    $this->db->add('test-1.xml', '<root><test/><test/></root>');
    $this->db->add('test-2.xml', '<root><test/></root>');

    $results = $this->db->xpath('//test');

    $this->assertNotEmpty($results);

    $this->assertEquals(3, count($results));

    foreach ($results as $r) {
      $this->assertXmlStringEqualsXmlString($r, '<test/>');
    }

    $results = $this->db->xpath('//test', 'test-1.xml');

    $this->assertNotEmpty($results);

    $this->assertEquals(2, count($results));
    foreach ($results as $r) {
      $this->assertXmlStringEqualsXmlString($r, '<test/>');
    }
  }
  
  /**
   * @expectedException BaseX\Error
   */
  public function testGetTree()
  {
    $this->db->add('test/test.xml', '<test/>');
    $this->db->store('test/test.txt', 'test');
    
    $this->assertInstanceOf('BaseX\Resource\Tree', $this->db->getTree());
    $this->assertInstanceOf('BaseX\Resource\Tree', $this->db->getTree('test'));
    $this->assertInstanceOf('BaseX\Resource\Tree', $this->db->getTree('test', 1));
    $this->assertInstanceOf('BaseX\Resource\Tree', $this->db->getTree('te', 1));
    
  }

}