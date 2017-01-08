<?php
namespace gusenru;

/**
  * PDO wrapper
  *
  * A simple class for:
  * - unified error handler
  * - easy queries monitor
  * - query results caching
  *
  * @param string $host MySQLi database ip-address
  * @user string $user database user name
  * @pass string $pass database user pass
  * @db string $db database name
  *
  * @return void
  */
class CDataBase extends \PDO 
{
	private $host;
	private $user;
	private $pass;
	private $db;
    private $isConnected;

	/**
	 * Prepare data. Don't connect until it is necessary. Many web-pages
	 * don't need database at all (ajax photo upload page, oAuth for example)
	 */
    function __construct($host, $user, $pass, $db) {
    	$this->host = $host;
    	$this->user = $user;
    	$this->pass = $pass;
    	$this->db = $db;
    	$this->isConnected = FALSE;
    }
    
    function __destruct () {
        $this->isConnected = FALSE;
    }
    
    private function connect() {
		try {
			$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
				$this->host,
				$this->db,
				DB_CHARSET);

	    	parent::__construct(
	        	$dsn,
	        	$this->user,
	        	$this->pass,
	        	array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
	    	);
	    	$this->isConnected = TRUE;
		}
		catch (\PDOException $ex) {
			throw new \Exception($ex->getMessage());
		}
    }
    
    public function query($q) {
    	if (!$this->isConnected)
    		$this->connect();
        try {
            return parent::query($q);
        }
        catch(\PDOException $ex) {
			throw new \Exception($ex->getMessage());
        }        
    }

    public function exec($q) {
    	if (!$this->isConnected)
    		$this->connect();
        try {
            return parent::exec($q);
        }
        catch(\PDOException $ex) {
			throw new \Exception($ex->getMessage());
        }        
    }
    
    public function prepare($sql, $options = array()) {
    	if (!$this->isConnected)
    		$this->connect();
		return parent::prepare($sql, $options);
    }

}

?>