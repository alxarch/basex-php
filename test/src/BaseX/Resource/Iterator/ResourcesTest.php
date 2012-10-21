<?php

namespace BaseX\Resource\Iterator;

use BaseX\PHPUnit\TestCaseDb;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-21 at 14:42:23.
 */
class ResourcesTest extends TestCaseDb
{

  /**
   * @var Resources
   */
  protected $iterator;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  public function setUp()
  {
    parent::setUp();
    $this->iterator = new Resources($this->db);
    $this->db->store('test.txt', 'test');
    $this->db->add('test.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    $this->db->add('test/.protect/hidden.xml', '<test/>');
    $this->db->add('test/test.xml', '<test/>');
    $this->db->add('sa/test.xml', '<test/>');
    $this->db->add('test/path/test.xml', '<test/>');
    
  }

 

  /**
   * @covers BaseX\Resource\Iterator\Resources::setPath
   */
  public function testSetPath()
  {
    $it = $this->iterator->setPath('test');
    
    $this->assertTrue($it instanceof Resources);
    $this->assertEquals(4, $this->iterator->getIterator()->count());
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::exclude
   * @todo   Implement testExclude().
   */
  public function testExclude()
  {
    $res =$this->iterator->exclude('@^test/.*@');
    $this->assertTrue($res instanceof Resources);
    
    $this->assertEquals(3, $this->iterator->getIterator()->count());
  }
  /**
   * @covers BaseX\Resource\Iterator\Resources::exclude
   * @todo   Implement testExclude().
   */
  public function testExclude2()
  {
    $res = $this->iterator->exclude('@[/^]\.protect/@');
    $this->assertTrue($res instanceof Resources);
    $this->assertEquals(6, $this->iterator->getIterator()->count());
    
    foreach ($res as $r)
    {
      $this->assertNotEquals('test/.protect/hidden.xml', $r->getPath());
    }
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::withTimestamps
   */
  public function testWithTimestamps()
  {
    foreach ($this->iterator->withTimestamps() as $resource)
    {
       $this->assertTrue($resource instanceof \BaseX\Resource);
       $this->assertNotNull($resource->getModified());
    }
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::bySize
   * @todo   Implement testBySize().
   */
  public function testBySize()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::byModified
   * @todo   Implement testByModified().
   */
  public function testByModified()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::byContentType
   * @todo   Implement testByContentType().
   */
  public function testByContentType()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::byPath
   * @todo   Implement testByPath().
   */
  public function testByPath()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::byType
   * @todo   Implement testByType().
   */
  public function testByType()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::reverse
   * @todo   Implement testReverse().
   */
  public function testReverse()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::getIterator
   */
  public function testGetIterator()
  {
    foreach ($this->iterator as $resource)
    {
      $this->assertTrue($resource instanceof \BaseX\Resource);
    }
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::convertResource
   * @todo   Implement testConvertResource().
   */
  public function denormalize()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Resource\Iterator\Resources::begin
   */
  public function testBegin()
  {
    $it = Resources::begin($this->db)->setPath('test');
    $this->assertTrue($it instanceof Resources);
    
    foreach ($it as $r)
    {
      $this->assertInstanceOf('BaseX\Resource', $r);
    }
  }

}
