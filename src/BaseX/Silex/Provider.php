<?php

namespace BaseX\Silex;

use Silex\Application;

use Silex\ServiceProviderInterface;

use BaseX\Session;

class Provider implements ServiceProviderInterface
{
  public function register(Application $app)
  {
    $app['basex'] = $app->share(function() use ($app){

      $host = isset($app['basex.host']) ? $app['basex.host'] : 'localhost';
      $port = isset($app['basex.port']) ? $app['basex.port'] : 1984;
      $user = isset($app['basex.user']) ? $app['basex.user'] : 'admin';
      $pass = isset($app['basex.pass']) ? $app['basex.pass'] : 'admin';
      
      try{
        $session = new Session($host, $port, $user, $pass);
      }
      catch(\Exception $e){
        throw $e;
      }
      return $session;
    });
  }
  
  public function boot(Application $app){
  }
}