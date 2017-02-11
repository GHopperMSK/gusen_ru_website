<?php
session_start();

require_once('config.php');
require_once('vendor/autoload.php');

// set error handler
new gusenru\CExceptionHandler();

echo gusenru\CWebPage::getInstance()->getContent();

?>
