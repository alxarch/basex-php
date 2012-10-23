<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Query;

use ArrayAccess;
use Countable;
use Iterator;

/**
 *
 * @author alxarch
 */
interface QueryResultsInterface extends ArrayAccess, Iterator, Countable
{
  public function addResult($data, $type);
  public function supportsType($type);
  public function supportsMethod($method);
}
