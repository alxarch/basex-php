<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Resource;

use BaseX\Resource\Streamable;
use BaseX\StreamWrapper;
use BaseX\PHPUnit\TestCaseDb;
use BaseX\Helpers as B;

class GenericStreamable extends Streamable {

  public function creationMethod() {
    return 'store';
  }

  public function isRaw() {
    return $this->raw;
  }

  public function setSize($size) {
    
  }

  public $raw;

}

/**
 * Description of StreamableTest
 *
 * @author alxarch
 */
class StreamableTest extends TestCaseDb {

  protected $rawInfo = '<resource raw="true" content-type="image/jpeg" modified-date="2012-05-27T12:36:48.000Z" size="60751">image.jpg</resource>';
  protected $xmlInfo = '<resource raw="false" content-type="application/xml" modified-date="2012-05-27T13:38:33.988Z">collection/doc.xml</resource>';
  protected $rawStreamable;
  protected $xmlStreamable;

  public function setUp() {
    parent::setUp();
    StreamWrapper::register($this->session);

    $this->rawStreamable = new GenericStreamable($this->db, 'image.jpg', B::date('2012-05-27T12:36:48.000Z'));
    $this->rawStreamable->setContentType('image/jpeg');
    $this->xmlStreamable = new GenericStreamable($this->db, 'collection/doc.xml', B::date('2012-05-27T12:36:48.000Z'));
    $this->xmlStreamable->setContentType('application/xml');
  }

  public function testType() {
    $this->assertEquals('image/jpeg', $this->rawStreamable->getContentType());
    $this->assertEquals('application/xml', $this->xmlStreamable->getContentType());
  }

  public function testRead() {
    $this->db->add('original.xml', '<test/>');

    $original = new GenericStreamable($this->db, 'original.xml');

    $this->assertXmlStringEqualsXmlString('<test/>', $original->read());
  }

  public function testWrite() {
    $original = new GenericStreamable($this->db, 'original.xml');
    $original->write('<test/>');
    $this->assertXmlStringEqualsXmlString('<test/>', $this->doc('original.xml'));
  }

  public function testWriteFromResource() {
    $original = new GenericStreamable($this->db, 'original.xml');

    $file = DATADIR . DIRECTORY_SEPARATOR . 'test.xml';
    $original->write(fopen($file, 'r'));

    $this->assertXmlStringEqualsXmlFile($file, $this->doc('original.xml'));
  }

  public function testReadInto() {
    $into = fopen('php://temp', 'r+');

    $this->db->add('original.xml', '<test/>');

    $original = new GenericStreamable($this->db, 'original.xml');

    $result = $original->read($into);

    $this->assertFalse(false === $result);
    $this->assertTrue(is_int($result));
    $this->assertTrue($result > 0);

    rewind($into);

    $contents = stream_get_contents($into);

    $this->assertXmlStringEqualsXmlString('<test/>', $contents);

    fclose($into);
  }

  public function testGetContentsRawInto() {
    $into = fopen('php://temp', 'r+');

    $contents = md5(time());
    $this->db->store('test.txt', $contents);

    $original = new GenericStreamable($this->db, 'test.txt');

    $result = $original->read($into);

    $this->assertFalse(false === $result);
    $this->assertTrue(is_int($result));
    $this->assertTrue($result > 0);

    rewind($into);

    $actual = stream_get_contents($into);

    $this->assertEquals($contents, $actual);

    fclose($into);
  }

  public function testReadRaw() {
    $contents = md5(time());

    $this->db->store('test.txt', $contents);

    $original = new GenericStreamable($this->db, 'test.txt');

    $this->assertEquals($contents, $original->read());
  }

  public function testGetUri() {
    $this->assertEquals("basex://$this->dbname/image.jpg", $this->rawStreamable->getUri());
    $this->assertEquals("basex://$this->dbname/collection/doc.xml", $this->xmlStreamable->getUri());
  }

  public function testGetStream() {
    $this->db->add('test.xml', '<root/>');

    $res = new GenericStreamable($this->db, 'test.xml');

    $this->assertTrue(is_resource($res->getStream()));
    $this->assertTrue(is_resource($res->getStream('w')));
  }

  /**
   * @expectedException BaseX\Error
   */
  public function testGetStreamNonExisting() {

    $res = new GenericStreamable($this->db, 'test.xml');
    $res->getStream();
  }

  public function tearDown() {
    StreamWrapper::unregister();
    parent::tearDown();
  }

  public function testRefresh() {
    $this->db->add('test.xml', '<root/>');
    $this->db->store('test.txt', 'root');

    $res = new GenericStreamable($this->db, 'test.xml');

    $r = $res->refresh();
    $this->assertTrue($r instanceof GenericStreamable);
    $start = $res->getModified()->format('Y-m-d\TH:i:s.uP');


    $this->db->replace('test.xml', '<replaced/>');

    $res->refresh();

    $end = $res->getModified()->format('Y-m-d\TH:i:s.uP');

    $this->assertNotEquals($end, $start);

    $res = new GenericStreamable($this->db, 'test.txt');
    $res->refresh();
    $start = $res->getModified()->format('Y-m-d\TH:i:s.uP');
    $res->refresh();
    $end = $res->getModified()->format('Y-m-d\TH:i:s.uP');
  }

  public function testRefreshChanged() {
    $this->db->add('test.xml', '<root/>');

    $res = new GenericStreamable($this->db, 'test.xml');
    $res->raw = false;
    $r = $res->refresh();

    $this->assertTrue($r instanceof GenericStreamable);

    $this->db->store('test.xml', 'root');

    $this->assertNull($res->refresh());
  }

}

