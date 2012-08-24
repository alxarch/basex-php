<?php

define('BASEX_HOST', 'localhost');
define('BASEX_PORT', 1984);
define('BASEX_PASS', 'admin');
define('BASEX_USER', 'admin');

spl_autoload_register(function($class){
  $path = str_replace('\\', '/', $class);
  $file = sprintf('%s/../src/%s.php', __DIR__, $path);
  if(!file_exists($file))
    $file = sprintf('%s/%s.php', __DIR__, $path);
  require_once($file);
});