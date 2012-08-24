<?php

namespace BaseX\Tests;

use BaseX\TestCaseDb;
use BaseX\Session;
use BaseX\Database;


class DatabaseTest extends TestCaseDb
{
  public function testInit()
  {
    $this->assertContains($this->dbname, $this->session->execute("LIST"));
  }
  
//  public function testDrop()
//  {
//    $this->db->drop();
//    $this->assertNotContains($this->dbname, $this->session->execute('LIST'));
//    $this->db = new Database($this->session, $this->dbname);
//  }
  
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
    
    $this->assertContains($path, $this->ls());
    
    $this->assertXmlStringEqualsXmlString($input, $this->doc($path));
  }
  
  /**
   * @depends testInit 
   */
  public function testStore()
  {
    $path = 'test.txt';
    $input = 'This is a test.';
    
    $this->db->store($path, $input);
    
    $this->assertContains($path, $this->ls());
    
    $contents = $this->raw($path);
    
    $this->assertEquals($input, $contents);
  }
  
  
  /**
   * @depends testStore
   * @depends testAdd
   */
  public function testFetch()
  {
    $path = 'test.txt';
    $input = 'This is a test.';
    
    $this->db->store($path, $input);
    
    $contents = $this->db->fetch($path, true);
    
    $this->assertEquals($input, $contents);
    
    // Make sure the serializer is set back to the default.
    $this->assertEquals("SERIALIZER: \n", $this->session->execute('GET SERIALIZER'));
    
    $path = 'test.xml';
    $input = '<test>This is a test.</test>';
    
    $this->db->add($path, $input);
    
    $contents = $this->db->fetch($path);
    $this->assertXmlStringEqualsXmlString($input, $contents);
  }
  
  /**
   * @depends testAdd 
   */
  public function testDelete()
  {
    $path = 'test.xml';
    $input = '<test>This is a test.</test>';
    
    $this->db->add($path, $input);
    
    $this->db->delete($path);
    
    $this->assertNotContains($path, $this->ls());
  }
  
  /**
   * @depends testAdd 
   */
  public function testRename()
  {
    $old = 'old.xml';
    $new = 'new.xml';
    
    $input = '<test>This is a test.</test>';
    
    $this->db->add($old, $input);
    
    $this->db->rename($old, $new);
    $this->assertNotContains($old, $this->ls());
    $this->assertContains($new, $this->ls());
    
    $this->assertXmlStringEqualsXmlString($input, $this->doc($new));
  }
  
  /**
   * @depends testAdd 
   * @depends testStore
   */
  public function testReplace()
  {
    $path = "test.xml";
    $old = "<old/>";
    $new = "<new/>";
    
    $this->db->add($path, $old);
    $this->db->replace($path, $new);
    
    $this->assertXmlStringEqualsXmlString($new, $this->doc($path));
    
    $path = 'test.txt';
    $old = "old";
    $new = "new";
    
    $this->db->store($path, $old);
    $this->db->replace($path, $new);
    
    $this->assertEquals($new, $this->raw($path));
    
    $this->assertXmlStringEqualsXmlString("<new/>", $this->doc("test.xml"));
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
   * @depends testAdd 
   */
  public function testDocument()
  {
    
    $this->db->add('test.xml', '<root/>');
    
    $doc = $this->db->document('test.xml');
    $this->assertInstanceOf('BaseX\Document', $doc);
  }
  
  /**
   * @depends testAdd 
   */
  public function testGetResources()
  {
    
    $this->db->add('test-1.xml', '<test1/>');
    $this->db->add('test-2.xml', '<test2/>');
    $this->db->add('test-3.xml', '<test3/>');
    $this->db->store('test.txt', 'test');
    
    $resources = $this->db->getResources();
    
    $this->assertEquals(4, count($resources));
    
    $this->assertEquals('test-1.xml', (string) $resources[0]);
    
//    $this->assertEquals('test', $this->db->retrieve('test.txt'));
    
  }
  
  public function testAddXML()
  {
    $input = '<root/>';
    $path = 'test.xml';
    
    $this->db->addXML($path, $input);
    
    $this->assertContains($path, $this->ls());
    $actual = $this->doc($path);
    $this->assertXmlStringEqualsXmlString($input, $actual);
    
    $this->db->delete($path);
    
    $this->db->addXML(array($path => $input), null);
    $this->assertContains($path, $this->ls());
    $actual = $this->doc($path);
    $this->assertXmlStringEqualsXmlString($input, $actual);
    
    $this->assertResetAfterAdd();
  }
  
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
    
    $this->assertContains($path, $this->ls());
    
    $this->assertResetAfterAdd();
  }
  
  public function testAddJSON()
  {
    $json = '{"key": "value"}';
    
    $path = "test.json";
    
    $this->db->addJSON($path, $json);
    
    $this->assertContains($path, $this->ls());
    
    $this->assertResetAfterAdd();
  }
  
  public function testAddCSV()
  {
    $json = "val1, val2\nval1, val2";
    
    $path = "test.csv";
    
    $this->db->addCSV($path, $json);
    
    $this->assertContains($path, $this->ls());
    
    $this->assertResetAfterAdd();
  }
  
  protected function assertResetAfterAdd()
  {
    $actual = $this->session->execute('GET PARSER');
    $this->assertEquals("PARSER: \n", $actual);
    
    $actual = $this->session->execute('GET PARSEROPT');
    $this->assertEquals("PARSEROPT: \n", $actual);
    
    $actual = $this->session->execute('GET HTMLOPT');
    $this->assertEquals("HTMLOPT: \n", $actual);
    
    $actual = $this->session->execute('GET CREATEFILTER');
    $this->assertEquals("CREATEFILTER: \n", $actual);
  }
  

}