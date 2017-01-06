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
class CDataBase
{
    private $hDbConn = null;

    function __construct($host, $user, $pass, $db) {

        $this->hDbConn = new \PDO("mysql:host=$host;dbname=$db;charset=".DB_CHARSET, 
            $user,
            $pass
        );
    }
    
    function __destruct () {
        $this->hDbConn = null;
    }
    
    public function query($q) {
        if ($this->hDbConn) {
            try {
                return $this->hDbConn->query($q);
            } catch(PDOException $ex) {
                echo "Query: $q<br />";
                echo 'Error: '.$ex->getMessage().'<br />';
                exit;
            }        
        }
        else {
            echo 'There isn\'t active connection to DB!<br />';
            exit;
        }        
    }

    public function exec($q) {
        if ($this->hDbConn) {
            try {
                return $this->hDbConn->exec($q);
            } catch(PDOException $ex) {
                echo "Executing query: $q<br />";
                echo 'Error: ' .$ex->getMessage().'<br />';
                exit;
            }        
        }
        else {
            echo 'There isn\'t active connection to DB!<br />';
            exit;
        }        
    }
    
    public function prepare($q) {
        return $this->hDbConn->prepare($q);
    }

    public function lastInsertId(){
        return $this->hDbConn->lastInsertId();
    }
    
}

?>