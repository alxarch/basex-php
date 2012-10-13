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

use Silex\ServiceProviderInterface;

use BaseX\Session;

/**
 * Silex service provider for a basex session.
 * 
 * @author alxarch
 */
class Provider implements ServiceProviderInterface
{
  public function register(Application $app)
  {
    $app['basex'] = $app->share(function() use ($app){

      $host = isset($app['basex.host']) ? $app['basex.host'] : 'localhost';
      $port = isset($app['basex.port']) ? $app['basex.port'] : 1984;
      $user = isset($app['basex.user']) ? $app['basex.user'] : 'admin';
      $pass = isset($app['basex.pass']) ? $app['basex.pass'] : 'admin';
      
      $session = new Session($host, $port, $user, $pass);

      return $session;
    });
  }
  
  public function boot(Application $app){
  }
}