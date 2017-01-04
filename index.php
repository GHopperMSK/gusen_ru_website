<?php
session_start();

require_once("config.php");
require_once("class/CDataBase.php");
require_once("class/CWebPage.php");
require_once("class/CModule.php");

// setup DB connect
$hDbConn = new CDataBase(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$hPage = new CWebPage($hDbConn);
$hPage->renderTemplate();
echo $hPage->getPageContent();
?>