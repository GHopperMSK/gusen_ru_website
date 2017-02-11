<?php
namespace gusenru;

/**
  * PDO wrapper
  * Singleton pattern. Use CDataBase::getInstance() to get the object instance
  * instead of new CDataBase(...) (it is denied due to the constructor is
  * private)
  *
  * The wrapper is used for:
  * - unified error handler
  * - easy queries monitor
  * - db queries caching
  *
  * @param	string	$host	database ip-address
  * @param	string	$user	database user name
  * @param	string	$pass	database user pass
  * @param	string	$db		database name
  *
  * @return	void
  */
class CDataBase extends \PDO 
{
	private $_host;
	private $_user;
	private $_pass;
	private $_db;
    
    static private $_instance = NULL;

    function __construct($host, $user, $pass, $db) {
    	CWebPage::debug("CDataBase::__construct({$host},{$user},PASS,{$db})");
    	
    	$this->_host = $host;
    	$this->_user = $user;
    	$this->_pass = $pass;
    	$this->_db = $db;

		$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
			$this->_host,
			$this->_db,
			DB_CHARSET);
    	parent::__construct(
        	$dsn,
        	$this->_user,
        	$this->_pass,
        	array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
    	);
    }
    
    function __destruct () {
    	$this->_instance = NULL;
    }

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() { }

	static public function getInstance()  {
        if(self::$_instance == NULL) {
            self::$_instance = new self(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        }
        return self::$_instance;
    }
    
    public function query($q) {
        try {
            return parent::query($q);
        }
        catch(\PDOException $ex) {
			throw new \Exception($ex->getMessage());
        }        
    }

    public function exec($q) {
        try {
            return parent::exec($q);
        }
        catch(\PDOException $ex) {
			throw new \Exception($ex->getMessage());
        }        
    }
    
    public function prepare($sql, $options = array()) {
    	try {
			return parent::prepare($sql, $options);
        }
        catch(\PDOException $ex) {
			throw new \Exception($ex->getMessage());
        }        
    }

}


?>