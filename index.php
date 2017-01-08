<?php
session_start();

require_once('config.php');
require_once('vendor/autoload.php');

spl_autoload_register(function ($class_name) {
    $parts = explode('\\', $class_name);
    require 'class/' . end($parts) . '.php';	
});

new gusenru\CExceptionHandler(); // set error handler
//throw new \Exception('aaa');

// setup DB connect
$hDbConn = new gusenru\CDataBase(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$hPage = new gusenru\CWebPage($hDbConn);
$hPage->renderTemplate();
echo $hPage->getPageContent();
?>