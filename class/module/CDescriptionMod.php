<?php
namespace gusenru\module;

class CDescriptionMod extends \gusenru\CMod
{
    function __construct($param1, $param2) {
    	parent::__construct($param1, $param2);

        switch ($this->_param1) {
            case "search_page":
            	$this->_searchPage();
            	break;
            case "unit_page":
            	$this->_unitPage();
            	break;
        }
    }
    
    private function _searchPage() {
    	$hWebPage = \gusenru\CWebPage::getInstance();

        $q = '
        	SELECT u.id 
    		FROM units u
			JOIN cities c ON u.city_id=c.id
			JOIN regions r ON c.rd_id=r.id
    		WHERE is_arch=FALSE';

        if ($hWebPage->getGetValue('vType') != 0) {
            $q .= sprintf(" AND u.cat_id=%d",
            	$hWebPage->getGetValue('vType'));
        }
        if ($hWebPage->getGetValue('vManuf')) {
            $q .= sprintf(" AND u.manufacturer_id=%d",
            	$hWebPage->getGetValue(vManuf));
        }
        if ($hWebPage->getGetValue('vFedDistr')) {
            $q .= sprintf(" AND r.fd_id=%d",
            	$hWebPage->getGetValue(vFedDistr));
        }

        if ($hWebPage->getGetValue('offset')) {
            $iOffset = max($hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }        
        
        $q .= sprintf(" ORDER BY date DESC LIMIT %d,%d",
            ($iOffset-1)*PAGINATOR_SHOW_ON_PAGE,
            PAGINATOR_SHOW_ON_PAGE
        );

        $res = \gusenru\CDataBase::getInstance()->query($q);
        $aCategories = array();
        $aManufacturers = array();
        while ($ur = $res->fetch(\PDO::FETCH_ASSOC)) {
        	$unit = $hWebPage->getUnit($ur['id']);
        	if (!in_array($unit->category, $aCategories))
        		$aCategories[] = $unit->category;
        	if (!in_array($unit->manufacturer, $aManufacturers))
        		$aManufacturers[] = $unit->manufacturer;
        		
        }
        
		if (count($aCategories) > 0)
        	$sDescr = implode(', ', $aCategories);
        if (count($aManufacturers) > 0) {
        	if (count($aCategories) > 0)
        		$sDescr .= ', ';
        	$sDescr .= implode(', ', $aManufacturers);
        }
        if (strlen($sDescr) > 0)
        	$sDescr .= '. ';
        $sDescr .= 'Спецтехника б/у, продажа от собственника, конкурентные цены, лизинг.';
        
		$this->_addContent(array($sDescr));
    }

    private function _unitPage() {
    	$hWebPage = \gusenru\CWebPage::getInstance();

        if ($hWebPage->getGetValue('id') != 0) {
        	$unit = $hWebPage->getUnit(
        		$hWebPage->getGetValue('id')
        	);
        	$this->_addContent(array($unit->getDescription()));
        }
    }
}

?>
