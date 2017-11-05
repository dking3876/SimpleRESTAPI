## Installation

`composer require dking3876/simple-rest-api`

### Setup
An htaccess file should be set up to ensure CORS is enabled. The below is an example of a usuable configuration

```
<IfModule mod_headers.c>
Header add Access-Control-Allow-Origin "*"
Header add Access-Control-Allow-Headers "Origin, Content-Type, X-Auth-Token , Authorization, x-requested-with"
Header add Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS"
</IfModule>
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
</IfModule>
```

### Configuration
in your main file you can set up your configuration as an array and pass that array to the API constructor.
```php
$api = new SimpleRESTAPI\API($config);
```
The configuration array has the following indexes
- connection: For holding database credentials
- base: for setting a base path for your api allowing the use of versioning for different endpoints
- response: type of default response heade
- socket: for use with websockets
- paths: an array of url paths, request methods and responses

#### Connection: 
Simple REst api can help hold credentials for connection to database.  These credentials can than be accessed as static properties `\SimpleRESTAPI\API::$DBCreds->{host|port|database|username|password}`  
```php
$config = array(
    "connection" => array(
        "host"  => "localhost",
        "port"  => "",
        "database"  => "",
        "username"  => "",
        "password"  => ""
    )
);
```
#### Base
This is the starting base path for the api.  This path is used to help version your api.
```php
$config = array(
    "base"  => "/api/v2/"
);
```
#### Response
The default response header for all requests. You can override this by ....
```php
$config = array(
    "response"  => 'Content-Type: application/json'
);
```
#### Socket 
SimpleRESTAPI comes with a built in websocket. You can set a callable method for the following events
- onOpen: fires when a client makes a connection. A reference to the socketClass and the clientID used for the duration of the client session are passed as arguments. `(\SimpleRESTAPI\WebSocket $socket, $clientId)`
- onMessage: fires when a client sends a new message. A reference to the socketClass, the clientId sending the message, the message data, and the length of the message are passed as arguments. `(\SimpleRESTAPI\WebSocket $socket, $clientId, $data, $dataLength)`
- onTick: fires for each connected client, for each loop/tick (a tick is apx 8-12 seconds).  A reference to the socketClass and the clientID for the current 'tick' are passed as arguments. `(\SimpleRESTAPI\WebSocket $socket, $clientId)`
- onClose: Fires when a client disconnects from the service. A reference to the socketClass, the clientId disconnecting, and the disconnect status are passed as arguments. `(\SimpleRESTAPI\WebSocket $socket, $clientId, $status)`
```php
$sample = function(\SimpleRESTAPI\WebSocket $socket, $clientId, $status){

}
$config = array(
    "socket"    => [
        "host"  => "127.0.0.1",
        "port"  => "9002",
        "events"    => [
            "onOpen"  =>array('Test\\testing\\test_socket_controller', 'testOpen'), 
            "onMessage"   => 'Test\\testing\\test_socket_controller::testMessage',
            "onTick"    => function(\SimpleRESTAPI\WebSocket $socket, $clientId){

            },
            "onClose"     => $sample
        ]
    ]
);
```
#### Paths
Paths are an array of path arrays defined to know how to route the request. A path definition consists of:
- path: the defined path using regex. Some examples are provided below
            
            'path'  => '^projects/?$`
            'path' => '^projects/(?P<project_id>[^/]*)/?$`

    You can define paths using [named subpatterns for path regex definitions](https://www.regular-expressions.info/named.html)
- Request Method and callable function: This defintion provideds the function to call for the matchedd request method.  The callable can be any of the following examples.  

            'GET'       => function($params, $tokens){}
            'HEAD'      => $myNamedFunction
            'POST'      => 'MyNameSpace\\MyClass::$myStaticMethod'
            'PATCH'     => array('MyNameSpace\MyClass', 'myMethod')
            'PUT'       => array($myInstantiatedObject, 'myMethod')
            'DELETE'    => ($params, $tokens)=>{}

An array of GET,POST parameters is passed as the first argument to your function. An array of url Tokens ((?P<project_id>[^/]*) would equate to $tokens['project_id'])

### Main Script
Once you have set up your configuration array simply pass the configuration as the argument for the constructor of the api.
```php
$config = [
    'connection'    => ...
    ...
    'paths' => array(...)
]
$loader = require __DIR__.'/../vendor/autoload.php';
$api = new SimpleRESTAPI\API($config);

$api->router();
echo json_encode($api->response()); //used to encode the response to json.
```
