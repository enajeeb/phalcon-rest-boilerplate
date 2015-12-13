<?php

use \Phalcon\DI\FactoryDefault as PhDi;

error_reporting(E_ALL);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__));
}

try {

    include __DIR__ . "/config/bootstrap.php";

    /**
    * Handle the request
    */
    $di = new PhDi();
    $app = new Bootstrap($di);

    $app->run();

} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
} catch (PDOException $e){
    echo $e->getMessage();
}
