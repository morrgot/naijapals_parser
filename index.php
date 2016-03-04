<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 02.03.2016
 * Time: 23:50
 */

define('APP_PATH', __DIR__);
define('RUNTIME_PATH', realpath(__DIR__.'/runtime'));
define('START_TIME', time());

function v(){
    echo "\n";
    call_user_func_array('var_dump', func_get_args());
    echo "\n";
}

function p($a){
    echo "\n";
    print_r($a);
}

function pf(){
    echo "\n";
    print_r(call_user_func_array('sprintf', func_get_args()));
}

// load composer
require __DIR__ . '/vendor/autoload.php';

// load resources
(new \Phalcon\Loader())->registerNamespaces(
    array(
        'App' => APP_PATH
    )
)->register();

$di = require(APP_PATH.'/config/di.php');

(new \Phalcon\Cli\Console($di))->handle(['task' => 'App\Tasks\Main', 'action' => 'run']);

echo "\n\n";
pf('Memory usage: %f MB', round(memory_get_peak_usage()/(1024*1024), 2));
pf('Time spent: %f sec', round(microtime(true) - START_TIME, 6));
exit();