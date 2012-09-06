<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Resource\Generic;
use BaseX\Error;

/**
 * BaseX Resource for non xml files.
 *
 * @package BaseX 
 */
class Raw extends Generic
{
  protected function init()
  {
    if(!$this->isRaw())
    {
      throw new Error('Resource is not a raw file.');
    }
  }
}

?>
