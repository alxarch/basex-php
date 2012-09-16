<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Resource;

use BaseX\Query\QueryResultProvider;
use BaseX\Query\QueryBuilder;

/**
 * Description of ResourceInfoProvider
 *
 * @author alxarch
 */
class ResourceInfoProvider extends QueryResultProvider
{
  public function getResultClass() 
  {
    return 'BaseX\Resource\ResourceInfo';
  }
  
  public function getQueryBuilder() {
    return QueryBuilder::begin()
        ->setBody('db:list-details($db, $path)')
        ->addExternalVariable('db')
        ->addExternalVariable('path');
  }
}

