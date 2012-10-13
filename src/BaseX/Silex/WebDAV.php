<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Silex;

use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Silex\Application;

use Sabre_DAV_Server as DAVServer;

use BaseX\Error;
use BaseX\Database;
use BaseX\DAV\ObjectTree;

/**
 * Description of WebDAV
 *
 * @author alxarch
 */
class WebDAV implements ControllerProviderInterface, ServiceProviderInterface
{
  const METHODS_ALLOWED = 'OPTIONS|GET|HEAD|DELETE|PROPFIND|MKCOL|PUT|PROPPATCH|COPY|MOVE|REPORT|LOCK|UNLOCK';
  

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
      $app['webdav']->exec();
      exit();
    })
    ->method(self::METHODS_ALLOWED)
    ->assert('path', '.*');

    return $controllers;
  }
  
  public function register(Application $app) {

    $app['webdav'] = $app->share(function(Application $app){
      
      if(isset($app["webdav.db"]) && $app["webdav.db"] instanceof Database)
      {
        $db = $app["webdav.db"];
      }
      elseif(isset ($app["db"]) && $app["db"] instanceof Database)
      {
         $db = $app["db"];
      }
      else
      {
        throw new Error('No database defined for DAV Server.');
      }
      
      if(isset($app["webdav.baseuri"]))
      {
        $baseuri = $app["webdav.baseuri"];
      }
      else
      {
        $baseuri = webdav;
      }

      if(isset($app["webdav.root"]))
      {
        $root = $app["webdav.root"];
      }
      else
      {
        $root = '';
      }
      
      if(isset($app["webdav.localfiles"]))
      {
        $dir = sprintf('%s/%s/raw', $app['basex']->getInfo()->dbpath , $this->db);
      }
      else
      {
        $dir = false;
      }
      
      $tree = new ObjectTree($db, $root, $dir);
      $dav = new DAVServer($tree);
      
      $dav->setBaseUri( '/'.trim($baseuri, '/').'/' );
      
      if(isset($app["webdav.debug"]))
      {
        $dav->debugExceptions = true;
      }
      
      if(isset($app["webdav.plugins"]))
      {
        foreach ($app["webdav.plugins"] as $plugin)
        {
          $dav->addPlugin($plugin);
        }
      }
      
      return $dav;
      
    });
  }
    
  public function boot(Application $app){
      
  }
}
