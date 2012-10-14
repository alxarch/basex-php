<?php

namespace BaseX;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Database;
use BaseX\Resource\ResourceMapper;

class DatabaseTest extends TestCaseDb {
//  public function testBackup()
//  {
//    $this->db->add('test.xml', '<test/>');
//    $b = $this->db->backup();
//    $this->assertTrue($b instanceof Backup);
//    $this->assertNotEmpty($this->session->query("db:backups('$this->dbname')")->execute());
//  }
//  
//  public function testBackups()
//  {
//    $this->db->add('test.xml', '<test/>');
//    $this->db->backup();
//    sleep(1);
//    $this->db->backup();
//    $backups = $this->db->getBackups();
//    
//    $this->assertEquals(2, count($backups));
//    $this->assertTrue($backups[0] instanceof Backup);
//    $this->assertTrue($backups[1] instanceof Backup);
//  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Invalid database name.
   */
  public function test__construct() {
//    $name = 'nameok'.time();
//    $db = new Database($this->session, $name);
//    $this->assertContains($name, $this->session->execute('LIST'));
//    $this->session->execute("DROP DB $name");

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

    $this->assertContains($path, self::ls());

    $this->assertXmlStringEqualsXmlString($input, self::doc($path));

    return $path;
  }

  /**
   * @depends testInit 
   */
  public function testStore() {
    $path = 'test.txt';
    $input = 'This is a test.';

    $this->db->store($path, $input);

    $this->assertContains($path, self::ls());

    $contents = self::raw($path);

    $this->assertEquals($input, $contents);

    return $path;
  }

  /**
   * @depends testAdd 
   * @depends testStore
   */
  public function testDelete($doc, $raw) {

    $this->db->delete($doc);

    $this->assertNotContains($doc, self::ls());

    $this->db->delete($raw);

    $this->assertNotContains($raw, self::ls());
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
    $this->assertNotContains($old, self::ls());
    $this->assertContains($new, self::ls());

    $contents = self::doc($new);

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

    $this->assertXmlStringEqualsXmlString($new, self::doc($path));

    $this->db->delete($path);

    $path = 'test.txt';
    $old = "old";
    $new = "new";

    $this->db->store($path, $old);
    $this->db->replace($path, $new);

    $this->assertEquals($new, self::raw($path));

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

    $provider = new ResourceMapper($this->db);

    $resource = $this->db->getResource('test-1.xml', $provider);

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

    $resources = $this->db->getResources('', $provider);

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
//    $this->assertContains($path, self::ls());
//    $actual = self::doc($path);
//    $this->assertXmlStringEqualsXmlString($input, $actual);
//    
//    $this->db->delete($path);
//    
//    $this->db->addXML(array($path => $input), null);
//    $this->assertContains($path, self::ls());
//    $actual = self::doc($path);
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
//    $this->assertContains($path, self::ls());
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
//    $this->assertContains($path, self::ls());
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
//    $this->assertContains($path, self::ls());
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

}