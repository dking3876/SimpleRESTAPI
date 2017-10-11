<?php
namespace SimpleRESTAPI\ErrorHandler;
class InvalidPathException extends \Exception{
    
    /**
     * Exception Constructor
     *
     * @param [string:path] $path
     * @param integer $code
     * @param Exception $previous
     */
    public function __construct($path, $code = 0, Exception $previous = null) {
        
        parent::__construct("Invalid Path provided: '".$path."'", $code, $previous);
    }
}