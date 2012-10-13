<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\Resource\Interfaces;

use Serializable;
use BaseX\Resource\Interfaces\ResourceInterface;

/**
 * Interface for serializable resources.
 * 
 * @author alxarch
 */
interface SerializableResource extends ResourceInterface, Serializable
{
  
}
