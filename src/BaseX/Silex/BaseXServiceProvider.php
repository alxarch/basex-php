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
use BaseX\StreamWrapper;
use Silex\ServiceProviderInterface;
use BaseX\Database;
use BaseX\Session;

/**
 * Silex service provider for a BaseX session.
 * 
 * @author alxarch
 */
class BaseXServiceProvider implements ServiceProviderInterface
{
  
  public function register(Application $app)
  {
    $app['basex'] = $app->share(function(Application $app){
      $s = $app['basex.session'];
      
      $session = new Session($s['host'], $s['port'], $s['user'], $s['pass']);

      StreamWrapper::register($session);
      
      if(isset($app['basex.databases']))
      {
          
        $db  = $app['basex.databases'];
        if(!is_array($db)) $db = array($db);

        foreach ($db as $d)
        {
          $app['basex.db.'.$d] = $app->share(function() use ($app, $d){
            return new Database($app['basex'], $d);
          });
        }
      }
      
      return $session;

    });
  }

  public function boot(Application $app) {
    
  }
  
}
