<?php
namespace SimpleRESTAPI;
use SimpleRESTAPI\ErrorHandler\Error;
use SimpleRESTAPI\ErrorHandler\InvalidPathException;
use SimpleRESTAPI\ErrorHandler\InvalidCallBack;
use SimpleRESTAPI\AutoMapper;
use SimpleRESTAPI\Resolver;
use SimpleRESTAPI\WebSocket;
/**
 * API Class
 * @author Deryk W. King <dking3876@gmail.com>
 * @category Simple script for creating an API
 * @copyright 2017 dking3876
 * @license MIT
 * @link http://url.com
 * @version 1.0.0
 * @
 */
class API{
    // static $statusCodes = [
    //     'success'       => "HTTP/1.1 200 OK", //200 Successfull response that should include a response body dependant on the request
    //     'created'       => "HTTP/1.1 201 CREATED", //201 A resource has been created. ideally returning he created resource
    //     'accepted'      => "HTTP/1.1 202 ACCEPTED", //202 Request was accepted however could not be completed
    //     'noContent'     => "HTTP/1.1 204 NO CONTENT", //204 Request succesfull without returning any content
    //     'badRequest'    => "HTTP/1.1 400 BAD REQUEST", //400 Generic Client side error.
    //     'unauthorized'  => "HTTP/1.1 401 UNAUTHORIZED", //401 Invalid or no authorization provided Need to return www-authenticate header containing hte challange
    //     'forbidden'     => "HTTP/1.1 403 FORBIDDEN", //403 response was formed correctly but user doesn't have proper permission.
    //     'notFound'      => "HTTP/1.1 404 NOT FOUND", //404 Resource not found at URI
    //     'invalidMethod' => "HTTP/1.1 405 METHOD NOT ALLOWED", //405 Method not allowed. you must return the allowed methods
    //     'serverError'   => "HTTP/1.1 500 INTERNAL SERVER ERROR" //500 SERVER ERROR
    // ];
    /**
     * Holds the reponse to return to the client
     *
     * @var array/object
     */ 
    private $response;
    /**
     * Last Error to return to the client 
     *
     * @var object
     */
    public $errors;
    /**
     * Http request Verb (Get,Post,Put,Patch,Delete)
     *
     * @var string
     */
    public $verb;
    /**
     * All Data sent from the client
     *
     * @var array
     */
    public $body = array();
    /**
     * Format of the request 
     *
     * @var string
     */
    public $format;
    /**
     * API Endpoint definitions
     *
     * @var array
     */
    private $endpoints = array();
    /**
     * Requested Entity
     *
     * @var string
     */
    public $noun;
    /**
     * API Endpoint found
     *
     * @var boolean
     */
    private $accepted = false;
    /**
     * Current API path
     *
     * @var string
     */
    public $current_path;
    /**
     * Default API Configuration to merge into the user privided configuration array
     *
     * @var array
     */
    private $default_config = array(
        "base"          => "",
        "format"        => "json",
        "paths"         => array()
    );
    /**
     * Connection Variables
     * 
     * @var object
     */
    static $DBCreds;
    /**
     * Instantiate the API Object
     *
     * @param array $config
     */
    static $SOCKET;
    public function __construct($config){
        $this->config = array_merge($this->default_config, $config);
        self::$DBCreds = (object)$this->config['connection'];
        unset($this->config['connection']);
        if(!isset($_SERVER['REQUEST_METHOD'])){
            if($config['socket']){
                $this->startWebSocket();
            }
        }else{
            $this->verb = $_SERVER['REQUEST_METHOD'];
            $this->uri = str_replace(
                    $this->config['base'], 
                    "", 
                    $_SERVER['REQUEST_URI']
                );
            $this->parse_data();
            $this->endpoints = $config['paths'];
        }
    }

    public function startWebSocket(){
        self::$SOCKET = new WebSocket();

        if( isset($this->config['socket']['events']['onOpen']) ){
            self::$SOCKET->bind('onOpen',$this->config['socket']['events']['onOpen']);
        }
        if( isset($this->config['socket']['events']['onMessage']) ){
            self::$SOCKET->bind('onMessage',$this->config['socket']['events']['onMessage']);
        }
        if( isset($this->config['socket']['events']['onMasterTick']) ){
            self::$SOCKET->bind('onMasterTick',$this->config['socket']['events']['onMasterTick']);
        }
        if( isset($this->config['socket']['events']['onTick']) ){
           self::$SOCKET->bind('onTick',$this->config['socket']['events']['onTick']);
        }
        if( isset($this->config['socket']['events']['onClientTick']) ){
            self::$SOCKET->bind('onTick',$this->config['socket']['events']['onClientTick']);
         }
        if( isset($this->config['socket']['events']['onClose']) ){
            self::$SOCKET->bind('onClose',$this->config['socket']['events']['onClose']);
        }
        
        self::$SOCKET->wsStartServer($this->config['socket']['host'], $this->config['socket']['port']);
        
    }
    /**
     * Parse the Data passed to the api
     * 
     * All data except POST is retrieved by php://input
     * POST Data is parsed via $_POST
     * 
     * Accepts: [application/json, appliation/x-www-form-urlencoded,multipart/form]
     * 
     * All $_FILES input is passed to your functions/methods in $params['file']
     * All POST/GET is passed in an array as the first argument
     *
     * @return void
     */
    public function parse_data(){
        /**
         * @var array Holds values from parsing the various forms of input
         */
        $parms = array();
        /**
         * @var Array Holds the data passed in the body of the request
         *  does not contain multipart/form data
         */
        $body = file_get_contents("php://input");
        $this->alt = $body;
        //get query
        if(isset($_SERVER['QUERY_STRING'])){
            parse_str($_SERVER['QUERY_STRING'], $parms);
        }
        $content_type = false;
        if(isset($_SERVER['CONTENT_TYPE'])){
            $content_type = explode(";", $_SERVER['CONTENT_TYPE'])[0];
        }
        
        //check content type and parse body appropriately
        switch($content_type) {
            case "application/json":
                $json_parms = json_decode($body);
                if($json_parms) {
                    foreach($json_parms as $param_name => $param_value) {
                        $parms[$param_name] = $param_value;
                    }
                }
                $this->format = "json";
                break;
            case "application/x-www-form-urlencoded":
                parse_str($body, $postvars);
                foreach($postvars as $field => $value) {
                    $parms[$field] = $value;
                }
                $this->format = "html";
                break;
            case "multipart/form-data":
                $postvars = $_POST;
                foreach($postvars as $field => $value) {
                    $parms[$field] = $value;
                }
                if($_FILES){
                    $parms['file'] = $_FILES;
                }
                $this->format = "html";
                break;
            default:
                
                // we could parse other supported formats here
                break;
        }
        $this->body = array_merge($this->body, $parms);
    }
    /**
     * Define API endpoints
     * 
     * Can be used at anypoint in the application prior to calling API::router()
     * Endpoints are checked in the order the are defined. To ensure the proper order 
     * it is best to define your endpoints in your configuration.  You can however define
     * endpoints at any point prior to calling the router() method
     *
     * Array passed to endpoints can be defined in any of the below three ways
     *  (function, array(object ,method), array(classname, method))
     * @param [Array] $endpoints 
     * @example  Sample Path array (
     *      'path'  => '^myendpoint/(?P<token>[/*])$', REGEX with pattern catches
     *      'GET'   => 'myFunction',
     *      'POST'  => array($obj, 'ObjMethod'),
     *      'PUT'   => array('\myNamespace\myClass', 'myMethod')
     * )
     * @param integer $priority 0 is default appends endpoint, 10 will jump the 
     *                definition to the begining of the array. Any other priority
     *                will divide the current number of definitions and insert the
     *                new definition at the perceived appropriate index
     * @return void
     * @todo need to add the functionality to determine all other priority besides 0 and 10
     */
    function define_endpoints($endpoints, $priority = 0){
        switch($priority){
            case $priority === 0:
            $this->endpoints = array_merge($this->endpoints, $endpoints);
            break;
            case $priority === 10:
            array_unshift($this->endpoints, $endpoints);
            break;
        }
    } 
    /**
     * Routes the client request to the appropriate path
     *
     * 
     * 
     * @throws \SimpleRESTAPI\InvalidCallBack\Exception 
     * @throws \SimpleRESTAPI\InvalidPathException
     * 
     * @return void
     * @todo provide the documentation for the router method
     */
    function router(){
        $parse = parse_url($this->uri);
        try{
            foreach($this->endpoints as $endpoint){
                if(preg_match("|".$endpoint['path']."|", rtrim($parse['path'], "/"), $matches) && !$this->accepted){
                    $this->accepted = true;
                    $this->current_path = $matches[0];
                    if( 
                        ( isset($endpoint[$this->verb]) && is_array($endpoint[$this->verb]) && method_exists($endpoint[$this->verb][0], $endpoint[$this->verb][1]) ) || 
                        ( ( isset($endpoint[$this->verb]) && is_callable($endpoint[$this->verb] ) ) || ( isset($endpoint[$this->verb]) && is_string($endpoint[$this->verb]) && function_exists($endpoint[$this->verb]) ) ) 
                    ){
                        $parameters = $this->parse_parameters($matches, $endpoint);
                        if( is_array($endpoint[$this->verb]) && !is_object($endpoint[$this->verb][0]) ){
                            
                            list($class, $method) = $endpoint[$this->verb];
                            $_class = $this->resolve($class);
                            $endpoint[$this->verb][0] = $_class;
                        }
                        $this->response = call_user_func_array($endpoint[$this->verb], array($parameters['parameters'],$parameters['url_tokens'], $this));
                    }else{
                        throw new InvalidCallBack(array($endpoint, $this->verb));
                    }
                }
            }
            if(!$this->accepted){
                throw new InvalidPathException($this->uri);
            }
        }catch(InvalidPathException $e){
           Error::$errors[] = "Invalid Path specified: \n\t {$e->getMessage()} ";
            // $this->error =  "Invalid Path specified: \n\t {$e->getMessage()} ";
            // $this->print_error();
        }catch(InvalidCallBack $e){
            Error::$errors[] = "Invalid CallBack: \n\t {$e->getMessage()}";
            // $this->print_error();
        }catch(\Exception $e){
            Error::$errors[] = $e->getMessage();
        }catch(\Error $e){
            Error::$errors[] = $e->getMessage();
        }

    }

    /**
     * Helper Method to instantiate classes with DI
     *
     * @param [ClassName] $class
     * @throws Exception
     * @return new Class Object
     */
    function resolve($class){
        try{
            return (new Resolver)->resolve($class);
        }catch(\Exception $e){
            Error::$errors[] =  $e->getMessage();
            // $this->print_error();
        }
    }
    /**
     * Parses the path matches and the captures to return the 
     * body parameters and the url tokens
     *
     * @param [array] $matches
     * @param [string] $endpoint
     * @return [array] (postData, regex path matches)
     */
    function parse_parameters($matches, $endpoint){
        //var_dump($matches);
        $keys = array_filter(array_keys($matches), function($arg){
            return is_string($arg);
        });
        //var_dump($keys);
        extract($matches);
        $new_parameters =  count($keys) > 0 ? 
            array( 
                "parameters"    => $this->body, 
                "url_tokens"    => compact($keys) 
                ) : 
            array(
                "parameters"  => $this->body, 
                "url_tokens"  => $matches
                );

        return $new_parameters;
    }
    /**
     * Check reponse for a value and return that value in the format
     * specified by the configuration with the appropriate http code
     * If no reponse return a 404 with the error provided in the error 
     * property
     *
     * @return json/xml
     */
    function response(){
        if($this->verb == "OPTIONS"){
            header("HTTP/1.0 200");
        }else
        // if(!$this->response){
        //     header("HTTP/1.0 404 Not Found");
        //     return array(
        //         "error" => $this->errors,
        //         "trace" => Error::$errors
        //     );
        // }else
        //  if(isset($this->response['error'])){
        if(Error::$errors){
            header("HTTP/1.0 404 Not Found");
            return array(
                'errors'    => Error::$errors
            );
        }else if(isset($this->response['code'])){
            $this->set_headers($this->response['code']);
            return array(
                'message'    => $this->response['body']
            );
        }else{
            return $this->response;
        }
    }
    /**
     * Set the Headers for the response
     *
     * @return void
     */
    function set_headers($header){
        header($header);

    }
    /**
     * Prints the response in the specified format
     *
     * @return void
     */
    function print_reponse(){
        $this->set_headers();
        switch($this->format){
            case 'json':
            echo \json_encode($this->response);
            break;
            case 'xml':
            //call something to generate xml
            break;
        }
    }
    /**
     * Prints out any caught errors
     *
     * @return void
     */
    function print_error(){
        $_error = array(
            "error" => $this->error,
            "trace" => Error::$errors
        );
        $this->set_headers();
        switch($this->format){
            case 'json':
            echo \json_encode($_error);
            break;
            case 'xml':
            //call something to generate xml
            break;
        }
        die();
    }
}