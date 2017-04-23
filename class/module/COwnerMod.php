<?php
namespace gusenru\module;

class COwnerMod extends \gusenru\CMod
{
    function __construct($param1, $param2) {
    	\gusenru\CWebPage::debug();
    	
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
    
    //------------------------------------------------------
    // Page functionality
    //------------------------------------------------------
    function _ownerList() {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
    	$aOwner = array();
    	
        $q = '
        	SELECT id
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
			$owner = new \gusenru\COwner($aRes[$i]['id']);
			$aOwner['owners']["owner{$i}"] = $owner->get()['owner'];
		}

		$this->_addContent($aOwner);
    }
    
    function _ownerForm() {
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
    	$owner = array();
    	
    	if ($hWebPage->getGetValue('id'))
    		$owner = (new \gusenru\COwner($hWebPage->getGetValue('id')))->get();

        $this->_addContent($owner);
    }
}

?>
