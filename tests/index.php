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
            "onOpen"  =>array('Test\\testing\\test_socket_controller', 'testOpen'), //when a new connection is opened from the client
            "onMessage"   => array('Test\\testing\\test_socket_controller', 'testMessage'), //When a new message is received from the client
            "onTick"    => array('Test\\testing\\test_socket_controller', 'testTick'), //This is the loop
            "onClose"     => array('Test\\testing\\test_socket_controller', 'testClose') //when a client closes the connection
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