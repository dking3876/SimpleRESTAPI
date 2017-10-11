<?php
namespace SimpleRESTAPI\ErrorHandler;

class InvalidCallBack extends \Exception{
    /**
 * Exception Constructor
 *
 * @param [string:path] $path
 * @param integer $code
 * @param Exception $previous
 */
public function __construct($errorData, $code = 0, Exception $previous = null) {
    parent::__construct("", $code, $previous);
    list($endpoint, $verb) = $errorData;
    if(isset($endpoint[$verb])){
        $this->message = "Invalid callback for {$endpoint['path']}";
    }else{
        $this->message = "No Callback defined for {$verb} request on {$endpoint['path']}";
    }
}
}