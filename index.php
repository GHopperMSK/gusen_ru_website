<?php
session_start();

require_once('config.php');
/*
class DB extends PDO
{
	function __construct() {
		echo "ccc";
	}
	
	function connect() {
		$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
			DB_HOST,
			DB_NAME,
			DB_CHARSET);

    	parent::__construct(
        	$dsn,
        	DB_USER,
        	DB_PASS,
        	array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    	);
	}
    
    public function query($q) {
        return	parent::query($q);
    }	
	
}


$db = new DB();
$db->connect();
$q = 'SELECT * FROM units ORDER BY date LIMIT 5';
$res = $db->query($q);
$row = $res->fetch(PDO::FETCH_ASSOC);
        
var_dump($row);
exit;
*/

spl_autoload_register(function ($class_name) {
    $parts = explode('\\', $class_name);
    require 'class/' . end($parts) . '.php';	
});

// setup DB connect
$hDbConn = new gusenru\CDataBase(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$hPage = new gusenru\CWebPage($hDbConn);
$hPage->renderTemplate();
echo $hPage->getPageContent();
?>