<?php
/**
 * @package BaseX
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */


namespace BaseX\Resource;

/**
 * Interface for BaseX Resources
 * 
 * @package BaseX
 */
interface ResourceInterface 
{
  public function copy($path);
  
  public function move($path);
  
  public function delete();
  
  public function isRaw();
  
  public function getSize();
  
  public function getModified();
  
  public function getType();
  
  public function getDatabase();
  
  public function getPath();
  
  public function getName();
  
  public function getURI();
  
  public function getEtag();
  
  public function reloadInfo();
  
}
