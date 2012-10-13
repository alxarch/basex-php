<?php
/**
 * @package BaseX
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */


namespace BaseX\Resource\Interfaces;

/**
 * Interface for BaseX Resources
 * 
 * @package BaseX
 */
interface ResourceInterface 
{
  public function copy($path);
  
  public function move($path);
  
  public function rename($name);
  
  public function delete();
  
  public function getModified();
  
  public function getDatabase();
  
  public function getPath();
  
  public function getName();
  
  public function getEtag();
  
  public function refresh();
  
  public function exists();
  
}
