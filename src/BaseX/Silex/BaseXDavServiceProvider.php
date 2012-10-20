<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Silex;

use Silex\Application;
use Sabre_DAV_Server;
use BaseX\Dav\ObjectTree;
use Silex\ServiceProviderInterface;

/**
 * Silex service provider for BaseX WebDAV Service.
 *
 * @author alxarch
 */
class BaseXDavServiceProvider implements ServiceProviderInterface
{

  const METHODS_ALLOWED = 'OPTIONS|GET|HEAD|DELETE|PROPFIND|MKCOL|PUT|PROPPATCH|COPY|MOVE|REPORT|LOCK|UNLOCK';

  public function register(Application $app)
  {

    $this->routes = array();

    $app['basex.dav'] = $app->share(function(Application $app){
      $servers = new \ArrayObject(array());
      
      foreach ($app['basex.dav.servers'] as $name => $opts)
      {
        if (isset($opts['db']))
          $db = $opts['db'];
        elseif (isset($app['basex.db.' . $name]))
          $db = $app['basex.db.' . $name];
        else
          throw new \LogicException("No database defined for DAV Server '$name'.");

        $path = isset($opts['path']) ? $opts['path'] : '';

        $dir = false;

        if (isset($app['basex.dav.localfiles']) && $opts['basex.dav.localfiles'])
        {
          $dbpath = $app['basex']->getInfo()->dbpath;
          $dir = implode(DIRECTORY_SEPARATOR, array_filter($dbpath, $db, 'raw', $path));
        }

        $root = new ObjectTree($db, $path, $dir);
        $dav = new Sabre_DAV_Server($root);

        $baseuri = isset($opts['baseuri']) ? '/' . trim($opts['baseuri'], '/') . '/' : "/webdav/$name/";

        $dav->setBaseUri($baseuri);

        if (isset($opts['debug']) && $opts['debug'])
          $dav->debugExceptions = true;

        if (isset($opts['plugins']))
        {
          foreach ($opts['plugins'] as $plugin)
          {
            $dav->addPlugin($plugin);
          }
        }
        
        $servers[$name] = $dav;
      }
      
      return $servers;
    });
  
  }

  public function boot(Application $app)
  {
   
    foreach ($app['basex.dav'] as $name => $server)
    {
      $pattern = sprintf("%s{path}", $server->getBaseUri());
      $app->match($pattern, function($path) use ($app, $server) {
          $server->exec();
          exit();
        })
      ->method(self::METHODS_ALLOWED)
      ->assert('path', '.*');
    }
  }

}
