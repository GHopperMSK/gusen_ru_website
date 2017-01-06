<?php
session_start();

spl_autoload_register(function ($class_name) {
    $parts = explode('\\', $class_name);
    require 'class/' . end($parts) . '.php';	
});

require_once('config.php');

// setup DB connect
$hDbConn = new gusenru\CDataBase(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$hPage = new gusenru\CWebPage($hDbConn);
$hPage->renderTemplate();
echo $hPage->getPageContent();
?>