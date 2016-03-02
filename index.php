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

// load composer
require __DIR__ . '/vendor/autoload.php';


// load resources
$loader = new \Phalcon\Loader();
$loader->registerNamespaces(
    array(
        'App' => APP_PATH
    )
)->register();

/**
 * @var $config object
 */
$config = new \Phalcon\Config\Adapter\Ini(__DIR__ .'/config.ini');

$di = new \Phalcon\Di\FactoryDefault\Cli();
$di->setShared('config', $config);

// setup database connection
$di->setShared('db', function () use ($config, $argv) {
    return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
        'host' => $config->database->host,
        'username' => $config->database->user,
        'password' => $config->database->password,
        'dbname' => $config->database->name,
        'options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        )
    ));
});

$app = new \Phalcon\Cli\Console($di);

$app->handle(['task' => 'App\Tasks\Main', 'action' => 'run']);