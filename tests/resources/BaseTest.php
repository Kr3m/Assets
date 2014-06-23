<?php 

class BaseTest extends \PHPUnit_Framework_TestCase {
    
    public function setVerboseErrorHandler()
    {
        $handler = function($errorNumber, $errorString, $errorFile, $errorLine) {
            echo "\nERROR INFO - Message: $errorString\nFile: $errorFile\nLine: $errorLine\n";
        };
        set_error_handler($handler);
    }
    
    //--------------------------------------------------------------------
    
}