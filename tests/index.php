<?php
header('Content-Type: application/json'); //@todo set this with the api instead.
$loader = require '../vendor/autoload.php';
$loader->addPsr4('Test\\testing\\', __DIR__);
use dking3876\SimpleRESTAPI\API;
// echo 'working';

$config = array(
    "connection" => array(
        "host"  => "localhost",
        "port"  => "",
        "database"  => "",
        "username"  => "",
        "password"  => ""
    ),
    "base"  => "/api/v2/",
    "paths" => array()
);



$config['paths'][] = array(
    'path'  => '^project/?$', 
    'GET'   => array('\Test\testing\test_controller', 'helloWorld'), 
    'POST'   => array('\Test\testing\test_controller', 'goodbyeWorld')
);

$api = new API($config);

$api->router();
echo json_encode($api->response());
?>