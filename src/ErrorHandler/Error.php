<?php
namespace SimpleRESTAPI\ErrorHandler;
/**
 * Error Handler
 * @todo Turn this into a singleton to be used statiically so i can just add errors on and log them somewhere 
 * and/or just return the errors with a stacktrace if no response
 */
class Error{
    static $success       = "HTTP/1.1 200 OK"; //200 Successfull response that should include a response body dependant on the request
    static $created       = "HTTP/1.1 201 CREATED"; //201 A resource has been created. ideally returning he created resource
    static $accepted      = "HTTP/1.1 202 ACCEPTED"; //202 Request was accepted however could not be completed
    static $noContent     = "HTTP/1.1 204 NO CONTENT"; //204 Request succesfull without returning any content
    static $badRequest    = "HTTP/1.1 400 BAD REQUEST"; //400 Generic Client side error.
    static $unauthorized  = "HTTP/1.1 401 UNAUTHORIZED"; //401 Invalid or no authorization provided Need to return www-authenticate header containing hte challange
    static $forbidden     = "HTTP/1.1 403 FORBIDDEN"; //403 response was formed correctly but user doesn't have proper permission.
    static $notFound      = "HTTP/1.1 404 NOT FOUND"; //404 Resource not found at URI
    static $invalidMethod = "HTTP/1.1 405 METHOD NOT ALLOWED"; //405 Method not allowed. you must return the allowed methods
    static $serverError   = "HTTP/1.1 500 INTERNAL SERVER ERROR"; //500 SERVER ERROR
    static $errors = array();

    private function __construct(){}
    static function ErrorHandler(\Error $error, $customMessage = ""){     
        // echo $customMessage . "\n";
        self::CustomHandler($error, $customMessage);
        
    }
    static function ExceptionHandler(\Exception $error, $customMessage = ""){
        // echo $customMessage."\n";
        self::CustomHandler($error, $customMessage);
        
    }
    static function CustomHandler($error, $customMessage){
        $_message = $error->getMessage();
        $_trace = $error->getTraceAsString();
        self::$errors[] = [
            'message'   => $_message.' : '.$customMessage,
            'trace'    => $_trace
        ];
        
    }
}


