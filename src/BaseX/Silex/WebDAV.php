<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BaseX\Silex;

use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Silex\Application;

use Sabre_DAV_Server as DAVServer;

use Sabre_DAV_TemporaryFileFilterPlugin as FilterTempPlugin;
use Sabre_DAV_Locks_Plugin as LocksPlugin;

use BaseX\Database;
use BaseX\DAV\Tree;
use BaseX\DAV\Locks\Backend as LocksBackend;

/**
 * Description of WebDAV
 *
 * @author alxarch
 */
class WebDAV implements ControllerProviderInterface
{
  const METHODS_ALLOWED = 'OPTIONS|GET|HEAD|DELETE|PROPFIND|MKCOL|PUT|PROPPATCH|COPY|MOVE|REPORT|LOCK|UNLOCK';

  protected $db;
  protected $baseuri;
  protected $local;
  
  /**
   * 
   * @param \BaseX\Database $db
   * @param string $baseuri
   * @param boolean $raw_files_local
   */
  public function __construct(Database $db, $baseuri, $raw_files_local=true) {
    $this->baseuri = '/'.trim($baseuri, '/').'/';
    $this->db = $db;
    $this->local = $raw_files_local;
  }
    
  /**
   * Returns routes to connect to the given application.
   *
   * @param Application $app An Application instance
   *
   * @return ControllerCollection A ControllerCollection instance
   */
  public function connect(Application $app)
  {
    $controllers = $app['controllers_factory'];

    // WebDAV service
    $controllers->match('{path}', function($path) use ($app){

      if($this->local)
      {
        // Specify the local path to serve raw files directly.
        $dir = sprintf('%s/%s/raw', $app['basex']->getInfo()->dbpath , $this->db);
      }
      else {
        $dir = false;
      }

      $root = new Tree($this->db, $dir);
      $dav = new DAVServer($root);

      // Filter garbage files
      $filter = new FilterTempPlugin();
      $dav->addPlugin($filter);

      // Support for locks
      $backend  = new LocksBackend($app['basex'], $this->db, '.protect/davlocks.xml');
      $dav->addPlugin(new LocksPlugin($backend));

      // Shows filename & line where the exception was thrown.
      $dav->debugExceptions = true;

      $dav->setBaseUri( $this->baseuri );

      $dav->exec();
      exit();
    })
    ->method(self::METHODS_ALLOWED)
    ->assert('path', '.*');

    return $controllers;
  
  }
}
