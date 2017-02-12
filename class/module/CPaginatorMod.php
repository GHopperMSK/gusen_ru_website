<?php
namespace gusenru\module;

class CPaginatorMod extends \gusenru\CMod
{
    function __construct($param1, $param2) {
		parent::__construct($param1, $param2);
		
        switch ($this->_param1) {
            case "search_page":
                $this->_searchPage();
                break;
            case 'arch_page':
            	$this->_archPage();
            	break;
            case 'owner_page':
            	$this->_ownerPage();
            	break;
        }
    }
    
    function _searchPage() {
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
        $q = '
        	SELECT
			COUNT(*) AS total
			FROM units
			JOIN cities ON units.city_id=cities.id
			JOIN regions ON cities.rd_id=regions.id
			JOIN fdistricts ON regions.fd_id=fdistricts.id
			JOIN categories ON units.cat_id=categories.id
			JOIN manufacturers ON manufacturers.id=units.manufacturer_id
			WHERE is_arch=FALSE';

        $bNeedAND = false;
        if ($hWebPage->getGetValue('vType') != 0) {
            $q .= sprintf(' AND cat_id=%d',
            	$hWebPage->getGetValue('vType'));
        }
        if ($hWebPage->getGetValue('vManuf')) {
            $q .= sprintf(' AND manufacturer_id=%d', 
            	$hWebPage->getGetValue(vManuf));
        }
        if ($hWebPage->getGetValue('vFedDistr')) {
            $q .= sprintf(' AND fd_id=%d', 
            	$hWebPage->getGetValue(vFedDistr));
        }

        $countRes = \gusenru\CDataBase::getInstance()->query($q);
        $iTotal = $countRes->fetch(\PDO::FETCH_ASSOC)['total'];

        // current page number
        if ($hWebPage->getGetValue('offset')) {
            $iOffset = max($hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }
        
        $vType = $hWebPage->getGetValue('vType') ?
        	$hWebPage->getGetValue('vTyep') : 0;
        $vManuf = $hWebPage->getGetValue('vManuf') ?
        	$hWebPage->getGetValue('vManuf') : 0;
        $vFedDistr = $hWebPage->getGetValue('vFedDistr') ?
        	$hWebPage->getGetValue('vFedDistr') : 0;

        switch ($hWebPage->getGetValue('page')) {
            case "search":
            	if (MOD_REWRITE) {
	            	$aVar = ['search', $vType, $vManuf, $vFedDistr, '%d'];
	            	$sLinkPattern = '/'.implode("/", $aVar);
            	}
            	else {
	            	$aVar = array(
	            		'page'		=> $hWebPage->getGetValue('page'),
	            		'vType'		=> $vType,
	            		'vManuf'	=> $vManuf,
	            		'vFedDistr'	=> $vFedDistr,
	            		'offset'	=> '%d'
	            	);
	            	$sLinkPattern = '/?'.http_build_query($aVar);
            	}
                break;
            case "admin":
            	$aVar = array(
            		'page'		=> $hWebPage->getGetValue('page'),
            		'act'		=> $hWebPage->getGetValue('act'),
            		'vType'		=> $vType,
            		'vManuf'	=> $vManuf,
            		'vFedDistr'	=> $vFedDistr,
            		'offset'	=> '%d'
            	);
            	$sLinkPattern = '?'.http_build_query($aVar);
                break;
        }
        
        $oPaginator = new \gusenru\CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );
        
        $this->_addContent($oPaginator->get());
    }
    
	function _archPage() {
		$hWebPage = \gusenru\CWebPage::getInstance();
        $q = '
        	SELECT
			COUNT(*) AS total
    		FROM units u
    		WHERE is_arch=TRUE';
        		
        $countRes = \gusenru\CDataBase::getInstance()->query($q);
        $iTotal = $countRes->fetch(\PDO::FETCH_ASSOC)['total'];

        // current page number
        if ($hWebPage->getGetValue('offset')) {
            $iOffset = max($hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }

    	$aVar = array(
    		'page'		=> $hWebPage->getGetValue('page'),
    		'act'		=> $hWebPage->getGetValue('act'),
    		'offset'	=> '%d'
    	);
    	$sLinkPattern = '?'.http_build_query($aVar);
        
        $oPaginator = new \gusenru\CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );
        
        $this->_addContent($oPaginator->get());
	}

    function _ownerPage() {
    	$hWebPage = \gusenru\CWebPage::getInstance();
        $q = '
        	SELECT
			COUNT(*) AS total
			FROM owners';

        $countRes = \gusenru\CDataBase::getInstance()->query($q);
        $iTotal = $countRes->fetch(\PDO::FETCH_ASSOC)['total'];

        // current page number
        if ($hWebPage->getGetValue('offset')) {
            $iOffset = max($hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }
        
    	$aVar = array(
    		'page'		=> $hWebPage->getGetValue('page'),
    		'act'		=> $hWebPage->getGetValue('act'),
    		'offset'	=> '%d'
    	);
    	$sLinkPattern = '?'.http_build_query($aVar);
        
        $oPaginator = new \gusenru\CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );

		$this->_addContent($oPaginator->get());
    }
}

?>
