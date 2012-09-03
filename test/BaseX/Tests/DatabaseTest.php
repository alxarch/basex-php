<?php

namespace BaseX\Tests;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Session;
use BaseX\Database;
use \InvalidArgumentException;

class DatabaseTest extends TestCaseDb
{
  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Invalid database name.
   */
  public function test__construct()
  {
    $name = 'nameok'.time();
    $db = new Database($this->session, $name);
    $this->assertContains($name, $this->session->execute('LIST'));
    
    $this->session->execute("DROP DB $name");
    $name = 'not ok όνομα';
    $db = new Database($this->session, $name);
  }
  
  public function testInit()
  {
    $this->assertContains($this->dbname, $this->session->execute("LIST"));
  }
  
  public function testGetName()
  {
    $this->assertEquals($this->dbname, $this->db->getName());
  }
  
  /**
   * @depends testInit 
   */
  public function testAdd()
  {
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
  public function testStore()
  {
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
  public function testDelete($doc, $raw)
  {
    
    $this->db->delete($doc);
    
    $this->assertNotContains($doc, self::ls());
    
    $this->db->delete($raw);
    
    $this->assertNotContains($raw, self::ls());
  }
  
  /**
   * @depends testDelete
   */
  public function testFetch()
  {
   
    $path = 'test 1.txt';
    $input = 'This is a test.';
    
    $this->db->store($path, $input);
    
    $contents = $this->db->fetch($path, true);
    
    $this->assertEquals($input, $contents);
    
    // Make sure the serializer is set back to the default.
    $this->assertEquals("SERIALIZER: \n", $this->session->execute('GET SERIALIZER'));
    $this->db->delete($path);
    
    $path = 'test.xml';
    $input = '<test>This is a test.</test>';
    
    $this->db->add($path, $input);
    
    $contents = $this->db->fetch($path);
    $this->assertXmlStringEqualsXmlString($input, $contents);
    $this->db->delete($path);

   
  }
  
  /**
   * @depends testDelete
   */
  public function testRename()
  {
    $old = 'old.xml';
    $new = 'new.xml';
    
    $input = '<test>This is a test.</test>';
    
    $this->db->add($old, $input);
    
    $this->db->rename($old, $new);
    $this->assertNotContains($old, self::ls());
    $this->assertContains($new, self::ls());
    
    $contents =  self::doc($new);
    
    $this->assertXmlStringEqualsXmlString($input, $contents);
    
    $this->db->delete($new);
  }
  
  /**
   * @depends testDelete
   */
  public function testReplace()
  {
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
  
  public function testExecute()
  {
    
    // Open another database.
    $this->session->execute('CHECK other');
    $this->session->execute('OPEN other');
    
    $info = $this->db->execute('INFO DB');
    
    $this->assertContains("Name: ".$this->dbname, $info);
    
    $this->session->execute('DROP DB other');
  }
  
  /**
   * @depends testDelete
   * 
   */
  public function testResource()
  {
    
    $this->db->add('test.xml', '<root/>');
    
    $doc = $this->db->resource('test.xml');
    $this->assertInstanceOf('BaseX\Resource', $doc);
    $this->assertTrue($doc->getDatabase() === $this->db);
    
    $doc = $this->db->resource('test.xml', 'BaseX\Document');
    $this->assertInstanceOf('BaseX\Document', $doc);
    $this->assertTrue($doc->getDatabase() === $this->db);
    
    $this->db->delete('test.xml');
    
    $this->assertNull($this->db->resource('test.xml'));
    
    
  }
  
  /**
   * @depends testResource
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Invalid class for resource.
   * 
   */
  public function testResourceException()
  {
    $this->db->add('test.xml', '<root/>');
    try{
      $doc = $this->db->resource('test.xml', 'StdClass');
    }
    catch (\InvalidArgumentException $e)
    {
      $this->db->delete('test.xml');
      throw $e;
    }
    $this->db->delete('test.xml');
    
  }
  
  /**
   * @depends testDelete
   */
  public function testGetResources()
  {
    $this->db->add('test-1.xml', '<test1/>');
    $this->db->add('dir/test-2.xml', '<test2/>');
    $this->db->add('dir/test-3.xml', '<test3/>');
    $this->db->store('test.txt', 'test');
    
     
    $resources = $this->db->getResources();
    
    $this->assertTrue(is_array($resources));
    $this->assertEquals(4, count($resources));
    
    foreach ($resources as $r)
    {
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
    
    $resources = $this->db->getResources('dir/');
    $this->assertTrue(is_array($resources));
    $this->assertEquals(2, count($resources));
    
    foreach ($resources as $r)
    {
      $this->assertInstanceOf('BaseX\Resource', $r);
    }
    
    $this->db->delete('test-1.xml');
    $this->db->delete('test-2.xml');
    $this->db->delete('test-3.xml');
    $this->db->delete('test.txt');
  }
  
  /**
   * @depends testDelete
   */
  public function testGetResourceInfo()
  {
    $this->db->add('test-1.xml', '<test1/>');
    $this->db->add('dir/test-2.xml', '<test2/>');
    $this->db->add('dir/test-3.xml', '<test3/>');
    $this->db->store('test.txt', 'test');
    
    $resources = $this->db->getResourceInfo();
    
    $this->assertTrue(is_array($resources));
    $this->assertEquals(4, count($resources));
    
    foreach ($resources as $r)
    {
      $this->assertInstanceOf('BaseX\Resource\Info', $r);
    }
    
    $resource = $resources[0];
    $this->assertEquals('test-1.xml', $resource->path());
    $resource = $resources[1];
    $this->assertEquals('dir/test-2.xml', $resource->path());
    $resource = $resources[2];
    $this->assertEquals('dir/test-3.xml', $resource->path());
    $resource = $resources[3];
    $this->assertEquals('test.txt', $resource->path());
    
    $resources = $this->db->getResourceInfo('dir/');
    $this->assertTrue(is_array($resources));
    $this->assertEquals(2, count($resources));
    
    foreach ($resources as $r)
    {
      $this->assertInstanceOf('BaseX\Resource\Info', $r);
    }
    
    $this->db->delete('test-1.xml');
    $this->db->delete('test-2.xml');
    $this->db->delete('test-3.xml');
    $this->db->delete('test.txt');
    
  }
  
  /**
   * @depends testDelete
   */
  public function testAddXML()
  {
    $input = '<root/>';
    $path = 'test.xml';
    
    $this->db->addXML($path, $input);
    
    $this->assertContains($path, self::ls());
    $actual = self::doc($path);
    $this->assertXmlStringEqualsXmlString($input, $actual);
    
    $this->db->delete($path);
    
    $this->db->addXML(array($path => $input), null);
    $this->assertContains($path, self::ls());
    $actual = self::doc($path);
    $this->assertXmlStringEqualsXmlString($input, $actual);
    
    $this->assertResetAfterAdd();
    
    $this->db->delete($path);
  }
  
  /**
   * @depends testDelete
   */
  public function testAddHTML()
  {
    $html = <<<HTML
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8">
        <title>Hello</title>
      </head>
      <body>
        <h1>Hello Test!</h1>
      </body>
    </html>
HTML;
    
    $path = "test.html";
    
    $this->db->addHTML($path, $html);
    
    $this->assertContains($path, self::ls());
    
    $this->assertResetAfterAdd();
    
    $this->db->delete($path);
  }
  
  /**
   * @depends testDelete
   */
  public function testAddJSON()
  {
    $json = '{"key": "value"}';
    
    $path = "test.json";
    
    $this->db->addJSON($path, $json);
    
    $this->assertContains($path, self::ls());
    
    $this->assertResetAfterAdd();
  }
  
  /**
   * @depends testDelete
   */
  public function testAddCSV()
  {
    $json = "val1, val2\nval1, val2";
    
    $path = "test.csv";
    
    $this->db->addCSV($path, $json);
    
    $this->assertContains($path, self::ls());
    
    $this->assertResetAfterAdd();
    
    $this->db->delete($path);
  }
  
  protected function assertResetAfterAdd()
  {
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
  public function testXpath()
  {
    $this->db->add('test-1.xml', '<root><test/><test/></root>');
    $this->db->add('test-2.xml', '<root><test/></root>');
    
    $result = $this->db->xpath('//test');
    
    $this->assertNotEmpty($result);
    
    $xml = simplexml_load_string("<root>$result</root>");
    
    $this->assertInstanceOf('\SimpleXmlElement', $xml);
    
    $this->assertEquals(3, count($xml->test));
    
    $result = $this->db->xpath('//test', 'test-1.xml');
    
    $this->assertNotEmpty($result);
    
    $xml = simplexml_load_string("<root>$result</root>");
    
    $this->assertInstanceOf('\SimpleXmlElement', $xml);
    
    $this->assertEquals(2, count($xml->test));
    
    $this->db->delete('test-1.xml');
    $this->db->delete('test-2.xml');
  }
  
  /**
   * @depends testDelete 
   */
  public function testGetContents()
  {
    $this->db->add('test-1.xml', '<test/>');
    $this->db->add('test-2.xml', '<test/>');
    $this->db->store('test.txt', 'test');
    $this->db->add('sa/test-1.xml', '<test/>');
    $this->db->add('sa/test-2.xml', '<test/>');
    $this->db->add('sa/sa/test-1.xml', '<test/>');
    
    $contents = $this->db->getContents();
    
    $this->assertInstanceOf('\SimpleXmlElement', $contents);
    
    $this->assertEquals(3, count($contents->resource));
    $this->assertEquals(1, count($contents->collection));
    $this->assertEquals('test-1.xml', (string) $contents->resource[0]);
    $this->assertEquals('sa', (string) $contents->collection[0]);
    
    $contents = $this->db->getContents('sa');
    $this->assertEquals(2, count($contents->resource));
    $this->assertEquals(1, count($contents->collection));
    
    $this->db->delete('test-1.xml');
    $this->db->delete('test-2.xml');
    $this->db->delete('test.txt');
    $this->db->delete('sa/test-1.xml');
    $this->db->delete('sa/test-2.xml');
    $this->db->delete('sa/sa/test-1.xml');
    
  }
}