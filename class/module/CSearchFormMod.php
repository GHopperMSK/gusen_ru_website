<?php
namespace gusenru\module;

class CSearchFormMod extends \gusenru\CMod
{
    function __construct($param1, $param2) {
    	\gusenru\CWebPage::debug();
    	
    	parent::__construct($param1, $param2);

        $this->_searchForm($this->_param1);
    }
    
    private function _searchForm($ref) {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	$hWebPage = \gusenru\CWebPage::getInstance();

		$aSearchForm = array();

		switch ($ref) {
			case 'search_ref':
				$aSearchForm['page'] = 'search';
				break;
			case 'admin_ref':
				$aSearchForm['page'] = 'admin';
				break;
			default:
				throw new \Exception('CSearchFormMod: unknown param1!');
		}
        
        $aRes = $hDbConn->query('
        	SELECT cat.id,cat.name
        	FROM categories cat
            JOIN units u ON cat.id=u.cat_id
            WHERE u.is_arch = FALSE
            GROUP BY cat.id HAVING count(cat.id) > 0
            ORDER BY name'
        	)->fetchAll(\PDO::FETCH_ASSOC);

        for ($i=0;$i<count($aRes);$i++) {
        	$aSearchForm['categories']["category{$i}"]['@content'] = 
        		$aRes[$i]['name'];
        	$aSearchForm['categories']["category{$i}"]['@attributes']['id'] = 
        		$aRes[$i]['id'];
			if ($hWebPage->getGetValue('vType') && 
        			$hWebPage->getGetValue('vType') == $aRes[$i]['id']) {
        		$aSearchForm['categories']["category{$i}"]
        			['@attributes']['selected'] = 'TRUE';
        	}
        }

        $aRes = $hDbConn->query('
        	SELECT
    			m.id,
    			m.name
    		FROM manufacturers m
    		JOIN units u ON m.id=u.manufacturer_id
    		WHERE u.is_arch = FALSE
    		GROUP BY m.id 
    		HAVING count(m.id)>0 
    		ORDER BY name'
        	)->fetchAll(\PDO::FETCH_ASSOC);

        for ($i=0;$i<count($aRes);$i++) {
        	$aSearchForm['manufacturers']["manufacturer{$i}"]['@content'] =
        		$aRes[$i]['name'];
        	$aSearchForm['manufacturers']["manufacturer{$i}"]
        		['@attributes']['id'] = $aRes[$i]['id'];
			if ($hWebPage->getGetValue('vManuf') && 
        			$hWebPage->getGetValue('vManuf') == $aRes[$i]['id']) {
        		$aSearchForm['manufacturers']["manufacturer{$i}"]
        			['@attributes']['selected'] = 'TRUE';
        	}
        }
        
        $aRes = $hDbConn->query('
        	SELECT f.id,f.name
        	FROM fdistricts f 
        	LEFT JOIN regions r ON r.fd_id=f.id 
        	LEFT JOIN cities c ON c.rd_id=r.id
        	LEFT JOIN units u ON u.city_id=c.id
        	WHERE u.is_arch = FALSE
        	GROUP BY f.id
        	HAVING COUNT(u.id)>0'
        	)->fetchAll(\PDO::FETCH_ASSOC);

        for ($i=0;$i<count($aRes);$i++) {
        	$aSearchForm['fdistricts']["fdistrict{$i}"]['@content'] =
        		$aRes[$i]['name'];
        	$aSearchForm['fdistricts']["fdistrict{$i}"]['@attributes']['id'] =
        		$aRes[$i]['id'];
			if ($hWebPage->getGetValue('vFedDistr') && 
        			$hWebPage->getGetValue('vFedDistr') == $aRes[$i]['id']) {
        		$aSearchForm['fdistricts']["fdistrict{$i}"]
        			['@attributes']['selected'] = 'TRUE';
        	}
        }

		$this->_addContent($aSearchForm);
    }
}

?>
