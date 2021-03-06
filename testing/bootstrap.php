<?php

/* 
 * This is the initializer required for starting up. Others may call this the includes file or the
 * init file.
 * We use the Autoloader to dynamically include items when they are required so that we dont load
 * everything every time which is not necessary.
 */

require_once(__DIR__ . '/settings.php');
require_once(__DIR__ . '/vendor/autoload.php');

$directories = array(
    __DIR__,
    __DIR__ . '/tests'
);

$autoloader = new \iRAP\Autoloader\Autoloader($directories);
