<?php
/**
 * Created by PhpStorm.
 * User: morrgot
 * Date: 02.03.2016
 * Time: 23:50
 */

define('APP_PATH', __DIR__);

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