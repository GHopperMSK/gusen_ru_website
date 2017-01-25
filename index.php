<?php
session_start();

require_once('config.php');
require_once('vendor/autoload.php');

// set error handler
new gusenru\CExceptionHandler();

// setup DB connect
$hDbConn = new gusenru\CDataBase(DB_HOST, DB_USER, DB_PASS, DB_NAME);

echo new gusenru\CWebPage($hDbConn);
?>
