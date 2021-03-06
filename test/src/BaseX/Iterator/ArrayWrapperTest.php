<?php

namespace BaseX\Iterator;

use BaseX\Iterator\ArrayWrapper;

class ArrayWrapperMock extends ArrayWrapper
{
  
  public function __construct($array)
  {
    $this->iterator = new \ArrayIterator($array);
  }
  public function getInitialIterator()
  {
    return $this->iterator;
  }
}

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-23 at 22:11:31.
 */
class ArrayWrapperTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @var ArrayWrapper
   */
  protected $wrapper;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  public function setUp()
  {
    $this->data = array('a', 'd', 'c', 'b', 'zoo', 'aardvark');
    $this->wrapper = new ArrayWrapperMock($this->data);
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::getIterator
   */
  public function testGetIterator()
  {
    $iterator = $this->wrapper->getIterator();
    
    $this->assertInstanceOf('ArrayIterator', $iterator);
    
    foreach ($iterator as $i => $value)
    {
      $this->assertEquals($this->data[$i], $value);
    }
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::getFirst
   */
  public function testGetFirst()
  {
    $this->assertEquals('a', $this->wrapper->getFirst());
    
    $w = new ArrayWrapperMock(array());
    
    $this->assertNull($w->getFirst());
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::getLast
   */
  public function testGetLast()
  {
    $this->assertEquals('aardvark', $this->wrapper->getLast());
    
    $w = new ArrayWrapperMock(array());
    
    $this->assertNull($w->getLast());
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::getSingle
   */
  public function testGetSingle()
  {
    $w = new ArrayWrapperMock(array('single'));
    $this->assertEquals('single', $w->getSingle());
    
    $this->assertNull($this->wrapper->getSingle());
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::count
   */
  public function testCount()
  {
    $this->assertEquals(6, $this->wrapper->count());
    
    $this->wrapper->grep('/^a.*/');
    
    $this->assertEquals(2, $this->wrapper->count());
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::sort
   */
  public function testSort()
  {
    $i = 0;
    $sort = function($a, $b){
      return $a == $b ? 0 : ($a > $b ? 1 : -1);
    };
    
    usort($this->data, $sort);
    $this->assertEquals($this->wrapper, $this->wrapper->sort($sort));
    foreach ($this->wrapper as  $value)
    {
      $this->assertEquals($this->data[$i], $value);
      $i++;
    }
    
    $this->assertEquals('a', $this->wrapper->getFirst());
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::filter
   * @todo   Implement testFilter().
   */
  public function testFilter()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::map
   * @todo   Implement testMap().
   */
  public function testMap()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::grep
   * @todo   Implement testGrep().
   */
  public function testGrep()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers BaseX\Iterator\ArrayWrapper::reverse
   * @todo   Implement testReverse().
   */
  public function testReverse()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

}
