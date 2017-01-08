<?php
namespace gusenru;

/**
 * Add two mode: debug, not debug
 * Send error log by email to admin in debug mode
 * Show "Sorry, we have a problem" in not debug mode
 */
class CExceptionHandler
{
    public function __construct() {
        set_exception_handler(array($this, 'errorProcess')); 
    }	
	
	public static function errorProcess($exception) {
		echo $exception;
		echo "=====>".$exception->getMessage(). "<br />";
		echo "=====>".$exception->getLine()(). "<br />";
	}
}

?>
