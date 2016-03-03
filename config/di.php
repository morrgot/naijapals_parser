<?php
/**
 * Created by PhpStorm.
 * User: aponirovskiy
 * Date: 03.03.2016
 * Time: 12:24
 */

$di = new \Phalcon\Di\FactoryDefault\Cli();
$di->setShared('config', function(){
    return new \Phalcon\Config\Adapter\Ini(__DIR__ .'/config/config.ini');
});

// setup database connection
$di->setShared('db', function () use ($di) {
    $config = $di->get('config');

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

$di->setShared('html_provider', function(){
   return new \App\Parsing\HtmlProvider();
});

$di->setShared('html_parser', function(){
    return new \App\Parsing\Parser();
});

return $di;