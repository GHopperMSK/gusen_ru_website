<?php
namespace gusenru;

class COwner
{
	private $_id = NULL;
	private $_name;
	private $_description;
	private $_link = array();
	
	function __construct($id=NULL) {
		CWebPage::debug("COwner::__construct({$id})");

        if (!empty($id))
        	$this->_fillOwnerData($id);
    }

    function __set($name, $val) {
    	if (property_exists($this, $name)) {
	    	$this->$name = $val;
    	}
    	else
    		throw new \Exception("Member 'COwner::{$name}' doesn't exists!");
    }

    function __get($name) {
    	if (property_exists($this, $name)) {
    		return $this->$name;
    	}
    	else
    		throw new \Exception("Member 'COwner::{$name}' doesn't exists!");
    }

    private function _fillOwnerData($id) {
    	$hDbConn = CDataBase::getInstance();
    	
    	$this->_id = $id;
    	
        $stmt = $hDbConn->prepare('
    		SELECT name,description
    		FROM `owners`
    		WHERE id=:id
    	');
    	
    	$stmt->bindValue(
    		':id',
    		$this->_id,
    		\PDO::PARAM_INT
    	);
		$stmt->execute();
        $aRes = $stmt->fetch(\PDO::FETCH_ASSOC);

		$this->_name = $aRes['name'];
		$this->_description = $aRes['description'];
		$this->_link['edit'] = "/?page=admin&act=owner_form&id={$this->_id}";
		$this->_link['delete'] = "/?page=admin&act=owner_delete&id={$this->_id}";
    }
    
    /**
     * Returns array of theowner data
     * 
     * @return array
     */
    function get() {
    	$aForm = array();
		$aForm['owner']['description'] = $this->_description;
		$aForm['owner']['links'] = $this->_link;
		
		$aForm['owner']['@attributes'] = array(
			'id' => $this->_id,
			'name' => $this->_name
		);

		return $aForm;
    }
    
    function add() {
    	$hDbConn = CDataBase::getInstance();
    	
        $stmt = $hDbConn->prepare('
    		INSERT INTO owners (
    			name,
    			description)
    		VALUES (
    			:name,
    			:descr)'
		);
        $stmt->bindValue(':name', $this->_name, \PDO::PARAM_STR);
        $stmt->bindValue(':descr', $this->_description, \PDO::PARAM_STR);
        $stmt->execute();
        $this->_id = $hDbConn->lastInsertId();
    }
    
    function edit() {
        $stmt = CDataBase::getInstance()->prepare('
    		UPDATE owners
    		SET
    			name=:name,
    			description=:descr
    		WHERE id=:owner_id'
		);
        $stmt->bindValue(':owner_id' ,$this->_id, \PDO::PARAM_INT);
        $stmt->bindValue(':name', $this->_name, \PDO::PARAM_STR);
        $stmt->bindValue(':descr', $this->_description, \PDO::PARAM_STR);
        $stmt->execute();
    }
    
    static function delete($id) {
    	$hDbConn = CDataBase::getInstance();
    	
        $q = sprintf('
    		SELECT COUNT(*) AS cnt
    		FROM `units`
    		WHERE owner_id=%d',
    		$id
    	);
        $res = $hDbConn->query($q);
        if ($res->fetch(\PDO::FETCH_ASSOC)['cnt'] > 0) {
        	throw new \Exception('The owner is used! Remove it from all units '.
        		'before deleting.');
        } else {
        	$q = sprintf('DELETE FROM `owners` WHERE id=%d', $id);
        	$hDbConn->query($q);
        }
    }
}
?>
