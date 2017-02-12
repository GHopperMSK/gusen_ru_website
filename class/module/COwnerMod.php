<?php
namespace gusenru\module;

class COwnerMod extends \gusenru\CMod
{
    function __construct($param1, $param2) {
    	parent::__construct($param1, $param2);

        switch ($this->_param1) {
            case 'owner_page':
            	$this->_ownerList();
            	break;
            case 'owner_form':
            	$this->_ownerForm();
            	break;
        }
    }
    
    function __destruct () {
    	\gusenru\CWebPage::debug('CModule::__destruct()');
    	
        $this->_aContent = NULL;
    }
    
    private function _addContent($aContent) {
    	$this->_aContent = array_merge($this->_aContent, $aContent);
    }

    function execute() {
    	return $this->_aContent;
    }
    
    //------------------------------------------------------
    // Page functionality
    //------------------------------------------------------
    function _ownerList() {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
    	$aOwner = array();
    	
        $q = '
        	SELECT id,name,description
        	FROM owners
        	ORDER BY name';
        	
        if ($hWebPage->getGetValue('offset')) {
            $iOffset = max($hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }        
        
        $q .= sprintf(" LIMIT %d,%d",
            ($iOffset-1)*PAGINATOR_SHOW_ON_PAGE,
            PAGINATOR_SHOW_ON_PAGE
        );

		$aRes = $hDbConn->query($q)->fetchAll(\PDO::FETCH_ASSOC);
		
		for ($i=0;$i<count($aRes);$i++) {
			$aOwner['owners']["owner{$i}"]['@content'] =
				$aRes[$i]['description'];
			$aOwner['owners']["owner{$i}"]['@attributes'] = array(
				'id' => $aRes[$i]['id'],
				'name' => $aRes[$i]['name']
			);
		}
		
		$this->_addContent($aOwner);
    }
    
    function _ownerForm() {
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
    	$aForm = array();
    	
        if ($hWebPage->getGetValue('id')) {
        	$hDbConn = \gusenru\CDataBase::getInstance();
        	
	        $stmt = $hDbConn->prepare('
	    		SELECT id,name,description
	    		FROM `owners`
	    		WHERE id=:owner_id
	    	');
	    	
	    	$stmt->bindValue(
	    		':owner_id',
	    		$hWebPage->getGetValue('id'),
	    		\PDO::PARAM_INT
	    	);
			$stmt->execute();
	        $aRes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

			$aForm['owner']['@content'] = $aRes[0]['description'];
			$aForm['owner']['@attributes'] = array(
				'id' => $aRes[0]['id'],
				'name' => $aRes[0]['name']
			);
        }
        
        $this->_addContent($aForm);
    }
}

?>
