<?php

define('BASEX_HOST', 'localhost');
define('BASEX_PORT', 1984);
define('BASEX_PASS', 'admin');
define('BASEX_USER', 'admin');
define('DATADIR', __DIR__.DIRECTORY_SEPARATOR.'data');
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->register();