<?php

namespace BaseX\Tests;

use BaseX\TestCaseDb;
use BaseX\Session;
use BaseX\Database;


class DatabaseTest extends TestCaseDb
{
  public function testInit()
  {
    $this->assertContains(self::$dbname, self::$session->execute("LIST"));
  }
  
//  public function testDrop()
//  {
//    self::$db->drop();
//    $this->assertNotContains(self::$dbname, self::$session->execute('LIST'));
//    self::$db = new Database(self::$session, self::$dbname);
//  }
  
  public function testGetName()
  {
    $this->assertEquals(self::$dbname, self::$db->getName());
  }
  
  /**
   * @depends testInit 
   */
  public function testAdd()
  {
    $path = 'test.xml';
    $input = '<test>This is a test.</test>';
    
    self::$db->add($path, $input);
    
    $this->assertContains($path, self::ls());
    
    $this->assertXmlStringEqualsXmlString($input, self::doc($path));
    
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
    
    self::$db->store($path, $input);
    
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
    
    self::$db->delete($doc);
    
    $this->assertNotContains($doc, self::ls());
    
    self::$db->delete($raw);
    
    $this->assertNotContains($raw, self::ls());
  }
  
  /**
   * @depends testDelete
   */
  public function testFetch()
  {
    $path = 'test.txt';
    $input = 'This is a test.';
    
    self::$db->store($path, $input);
    
    $contents = self::$db->fetch($path, true);
    
    $this->assertEquals($input, $contents);
    
    // Make sure the serializer is set back to the default.
    $this->assertEquals("SERIALIZER: \n", self::$session->execute('GET SERIALIZER'));
    self::$db->delete($path);
    
    $path = 'test.xml';
    $input = '<test>This is a test.</test>';
    
    self::$db->add($path, $input);
    
    $contents = self::$db->fetch($path);
    $this->assertXmlStringEqualsXmlString($input, $contents);
    self::$db->delete($path);
    
   
  }
  
  /**
   * @depends testDelete
   */
  public function testRename()
  {
    $old = 'old.xml';
    $new = 'new.xml';
    
    $input = '<test>This is a test.</test>';
    
    self::$db->add($old, $input);
    
    self::$db->rename($old, $new);
    $this->assertNotContains($old, self::ls());
    $this->assertContains($new, self::ls());
    
    $contents =  self::doc($new);
    
    $this->assertXmlStringEqualsXmlString($input, $contents);
    
    self::$db->delete($new);
  }
  
  /**
   * @depends testDelete
   */
  public function testReplace()
  {
    $path = "test.xml";
    $old = "<old/>";
    $new = "<new/>";
    
    self::$db->add($path, $old);
    self::$db->replace($path, $new);
    
    $this->assertXmlStringEqualsXmlString($new, self::doc($path));
    
    self::$db->delete($path);
    
    $path = 'test.txt';
    $old = "old";
    $new = "new";
    
    self::$db->store($path, $old);
    self::$db->replace($path, $new);
    
    $this->assertEquals($new, self::raw($path));
    
    self::$db->delete($path);
  }
  
  public function testExecute()
  {
    
    // Open another database.
    self::$session->execute('CHECK other');
    self::$session->execute('OPEN other');
    
    $info = self::$db->execute('INFO DB');
    
    $this->assertContains("Name: ".self::$dbname, $info);
    
    self::$session->execute('DROP DB other');
  }
  
  /**
   * @depends testDelete
   */
  public function testDocument()
  {
    
    self::$db->add('test.xml', '<root/>');
    
    $doc = self::$db->document('test.xml');
    $this->assertInstanceOf('BaseX\Document', $doc);
    $this->assertTrue($doc->getDatabase() === self::$db);
    
    self::$db->delete('test.xml');
    
    
    $this->assertNull(self::$db->document('not-here-doc'.time()));
  }
  
  /**
   * @depends testDelete
   */
  public function testGetResources()
  {
    self::$db->add('test-1.xml', '<test1/>');
    self::$db->add('test-2.xml', '<test2/>');
    self::$db->add('test-3.xml', '<test3/>');
    self::$db->store('test.txt', 'test');
    
    $resources = self::$db->getResources();
    
    $this->assertTrue(is_array($resources));
    $this->assertEquals(4, count($resources));
    
    foreach ($resources as $r)
    {
      $this->assertInstanceOf('BaseX\Resource\Info', $r);
    }
    
    $resource = $resources[0];
    $this->assertEquals('test-1.xml', $resource->path());
    $resource = $resources[1];
    $this->assertEquals('test-2.xml', $resource->path());
    $resource = $resources[2];
    $this->assertEquals('test-3.xml', $resource->path());
    $resource = $resources[3];
    $this->assertEquals('test.txt', $resource->path());
    
    self::$db->delete('test-1.xml');
    self::$db->delete('test-2.xml');
    self::$db->delete('test-3.xml');
    self::$db->delete('test.txt');
    
  }
  
  /**
   * @depends testDelete
   */
  public function testAddXML()
  {
    $input = '<root/>';
    $path = 'test.xml';
    
    self::$db->addXML($path, $input);
    
    $this->assertContains($path, self::ls());
    $actual = self::doc($path);
    $this->assertXmlStringEqualsXmlString($input, $actual);
    
    self::$db->delete($path);
    
    self::$db->addXML(array($path => $input), null);
    $this->assertContains($path, self::ls());
    $actual = self::doc($path);
    $this->assertXmlStringEqualsXmlString($input, $actual);
    
    $this->assertResetAfterAdd();
    
    self::$db->delete($path);
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
    
    self::$db->addHTML($path, $html);
    
    $this->assertContains($path, self::ls());
    
    $this->assertResetAfterAdd();
    
    self::$db->delete($path);
  }
  
  /**
   * @depends testDelete
   */
  public function testAddJSON()
  {
    $json = '{"key": "value"}';
    
    $path = "test.json";
    
    self::$db->addJSON($path, $json);
    
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
    
    self::$db->addCSV($path, $json);
    
    $this->assertContains($path, self::ls());
    
    $this->assertResetAfterAdd();
    
    self::$db->delete($path);
  }
  
  protected function assertResetAfterAdd()
  {
    $actual = self::$session->execute('GET PARSER');
    $this->assertEquals("PARSER: xml\n", $actual);
    
    $actual = self::$session->execute('GET PARSEROPT');
    $this->assertEquals("PARSEROPT: \n", $actual);
    
    $actual = self::$session->execute('GET HTMLOPT');
    $this->assertEquals("HTMLOPT: \n", $actual);
    
    $actual = self::$session->execute('GET CREATEFILTER');
    $this->assertEquals("CREATEFILTER: \n", $actual);
  }
  
//  /**
//   * @depends testDelete
//   */
//  public function testExists()
//  {
//    $this->assertFalse(self::$db->exists('file_'.time().'.xml'));
//    self::$db->add('test.xml', '<test/>');
//    $this->assertTrue(self::$db->exists('test.xml'));
//    self::$db->delete('test.xml');
//  }

  /**
   * @depends testDelete 
   */
  public function testXpath()
  {
    self::$db->add('test-1.xml', '<root><test/><test/></root>');
    self::$db->add('test-2.xml', '<root><test/></root>');
    
    $result = self::$db->xpath('//test');
    
    $this->assertNotEmpty($result);
    
    $xml = simplexml_load_string("<root>$result</root>");
    
    $this->assertInstanceOf('\SimpleXmlElement', $xml);
    
    $this->assertEquals(3, count($xml->test));
    
    $result = self::$db->xpath('//test', 'test-1.xml');
    
    $this->assertNotEmpty($result);
    
    $xml = simplexml_load_string("<root>$result</root>");
    
    $this->assertInstanceOf('\SimpleXmlElement', $xml);
    
    $this->assertEquals(2, count($xml->test));
    
    self::$db->delete('test-1.xml');
    self::$db->delete('test-2.xml');
  }
}