<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Query;

use BaseX\Query\QueryResultsInterface;

/**
 * Description of QueryResults
 *
 * @author alxarch
 */
class QueryResults implements QueryResultsInterface
{
  /**
   *
   * @var int
   */
  protected $current = 0;

  /**
   *
   * @var array
   */
  protected $data=array();
  protected $types=array();

  /**
   * 
   * @param scalar $data
   * @param int $type
   */
  public function addResult($data, $type)
  {
    $this->data[] = $data;
    $this->types[] = $type;
  }

  public function key() {
    return $this->current;
  }

  public function next() {
    $this->current++;
  }

  public function rewind() {
    $this->current = 0;
  }

  public function current() {
    return null === $this->data ? null : $this->data[$this->current];
  }

  public function valid() {
    return null !== $this->data && count($this->data) > $this->current;
  }

  public function offsetExists($offset) 
  {
    return null !== $this->data && isset($this->data[$offset]);
  }

  public function offsetGet($offset)
  {
    return null === $this->data ? null : (isset($this->data[$offset]) ? $this->data[$offset] : null);
  }

  public function offsetSet($offset, $value) {
    throw new Error('Result data is read-only.');
  }

  public function offsetUnset($offset) {
    throw new Error('Result data is read-only.');
  }

  public function getFirst()
  {
    return $this->offsetGet(0);
  }

  public function getLast()
  {
    return $this->offsetGet($this->count() - 1);
  }

  public function getSingle()
  {
    return (null !== $this->data &&  count($this->data) === 1) ? $this->offsetGet(0) : null;
  }

  public function count()
  {
    return null === $this->data ? 0 : count($this->data);
  }
  
  public function supportsType($type) {
    return true;
  }

  public function supportsMethod($method) {
    return true;
  }
}
