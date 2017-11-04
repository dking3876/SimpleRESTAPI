<?php
header('Content-Type: application/json'); //@todo set this with the api instead.
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('Test\\testing\\', __DIR__);
use SimpleRESTAPI\API;

$config = array(
    "connection" => array(
        "host"  => "localhost",
        "port"  => "",
        "database"  => "",
        "username"  => "",
        "password"  => ""
    ),
    "base"  => "/api/v2/",
    "response"  => 'Content-Type: application/json',
    "socket"    => [
        "host"  => "127.0.0.1",
        "port"  => "9002",
        "events"    => [
            "onOpen"  => "",
            "onMessage"   => "",
            "onTick"    => "", //This is the loop
            "onClose"     => ""
        ]
    ]
);



$paths[] = array(
    'path'  => '^project/?$', 
    'GET'   => array('Test\testing\test_controller', 'helloWorld'), 
    'POST'   => array('Test\testing\test_controller', 'goodbyeWorld')
);
$config['paths'] = $paths;
$api = new API($config);

$api->router();
echo json_encode($api->response());
?>