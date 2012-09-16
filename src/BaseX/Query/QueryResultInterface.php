<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Query;
/**
 *
 * @author alxarch
 */
interface QueryResultInterface 
{
  public function getData();
  public function setData($data);
  public function getType();
  public function setType($type);
  
  public static function getSupportedTypes();
}
