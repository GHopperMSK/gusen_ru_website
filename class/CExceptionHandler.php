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
		if (DEBUG_MODE) {
			header("Content-Type: text/plain");
			
			echo 'File: ', $exception->getFile();
			echo ' (', $exception->getLine(), ')'.PHP_EOL;
			echo 'Code (', $exception->getCode(), '): ';
			echo $exception->getMessage().PHP_EOL.PHP_EOL;
			print_r($exception->getTrace());
			exit;
		}
		else {
			$mail = new \PHPMailer;
			
			$mail->setFrom('noreplay@gusen.ru', 'gusen.ru web site');
			$mail->addAddress("admin@gusen.ru", "gusen.ru admin");

			$mail->isHTML(FALSE);
			$mail->Subject = "https://gusen.ru site error report";
			$mail->Body = $exception;
			
			$mail->send();

			header("Location: /error");
		}
	}
}

?>
