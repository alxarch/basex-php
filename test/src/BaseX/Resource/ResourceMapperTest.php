<?php

/**
 * @package BaseX
 * @subpackage Tests 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\PHPUnit\TestCaseDb;
use BaseX\Resource\Raw;
use BaseX\Resource\ResourceMapper;
use BaseX\Resource\Document;
use BaseX\Resource\Collection;
use BaseX\Helpers as B;
/**
 * Description of ResourceMapperTest
 *
 * @author alxarch
 */
class ResourceMapperTest extends TestCaseDb {

  /**
   * @expectedException BaseX\Error\ResultMapperError
   */
  function testWrongData() {
    $mapper = new ResourceMapper($this->db);
    $result = $mapper->getResult('<whatever/>', null);
  }

  function testRaw() {
    $data = '<resource raw="true" content-type="image/jpeg" modified-date="2012-05-27T12:36:48.000Z" size="60751">image.jpg</resource>';
    $mapper = new ResourceMapper($this->db);
    $result = $mapper->getResult($data, null);
    $this->assertTrue($result instanceof Raw);
    $this->assertEquals('image.jpg', $result->getPath());
    $this->assertEquals('image/jpeg', $result->getContentType());
    $this->assertEquals(B::date('2012-05-27T12:36:48.000Z'), $result->getModified());
  }

  function testCollection() {
    $data = '<collection modified-date="2012-05-27T12:36:48.000Z" path="somepath/test"/>';
    $mapper = new ResourceMapper($this->db);
    $result = $mapper->getResult($data, null);
    $this->assertTrue($result instanceof Collection);
    $this->assertEquals('somepath/test', $result->getPath());
    $this->assertEquals(B::date('2012-05-27T12:36:48.000Z'), $result->getModified());
  }

  function testDocument() {
    $data = '<resource raw="false" content-type="application/xml" modified-date="2012-05-27T13:38:33.988Z">collection/doc.xml</resource>';
    $mapper = new ResourceMapper($this->db);
    $result = $mapper->getResult($data, null);
    $this->assertTrue($result instanceof Document);
    $this->assertEquals('collection/doc.xml', $result->getPath());
    $this->assertEquals('application/xml', $result->getContentType());
    $this->assertEquals(B::date('2012-05-27T13:38:33.988Z'), $result->getModified());
  }

}

