<?php
namespace gusenru\module;

class CTitleMod extends \gusenru\CMod
{
    function __construct($param1, $param2) {
    	\gusenru\CWebPage::debug();
    	
		parent::__construct($param1, $param2);

        switch ($this->_param1) {
            case "unit_page":
                $this->_unitPage();
                break;
            case "search_page":
                $this->_searchPage();
                break;
        }
    }

    private function _unitPage() {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	$hWebPage = \gusenru\CWebPage::getInstance();

        if ($hWebPage->getGetValue('id')) {
            $q = sprintf('
            	SELECT
				CONCAT(m.name,\' \', u.name) as name 
    			FROM units u
    			JOIN manufacturers m
    				ON u.manufacturer_id=m.id
    			WHERE u.id=%d',
                $hWebPage->getGetValue('id'));
            $res = $hDbConn->query($q);
            $this->_addContent(
            	array(
            		$res->fetch(\PDO::FETCH_ASSOC)['name']
            	)
            );
        }
    }

    private function _searchPage() {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	$hWebPage = \gusenru\CWebPage::getInstance();

        $bFirst = true;

        if ($hWebPage->getGetValue('vType') > 0) {
            $stmt = $hDbConn->prepare('
            	SELECT name 
				FROM categories 
				WHERE id=:id'
			);
            $stmt->bindValue(':id',
            	$hWebPage->getGetValue('vType'),
            	\PDO::PARAM_INT
            );
            $stmt->execute();
            $sContent = $stmt->fetch(\PDO::FETCH_ASSOC)['name'];
            $bFirst = false;
        }
        if ($hWebPage->getGetValue('vManuf') > 0) {
            $stmt = $hDbConn->prepare('
            	SELECT name
				FROM manufacturers
				WHERE id=:id'
			);
            $stmt->bindValue(':id',
            	$hWebPage->getGetValue('vManuf'),
            	\PDO::PARAM_INT
            );
            $stmt->execute();
            if (!$bFirst)
                $sContent .= ' / ';
            else
                $bFirst = false;
            $sContent .= $stmt->fetch(\PDO::FETCH_ASSOC)['name'];
        }
        if ($hWebPage->getGetValue('vFedDistr') > 0) {
            $stmt = $hDbConn->prepare('
            	SELECT name 
				FROM fdistricts 
				WHERE id=:id'
			);
            $stmt->bindValue(':id',
            	$hWebPage->getGetValue('vFedDistr'),
            	\PDO::PARAM_INT
            );
            $stmt->execute();
            if (!$bFirst)
                $sContent .= ' / ';
            else
                $bFirst = false;
            $sContent .= $stmt->fetch(\PDO::FETCH_ASSOC)['name'];
        }    
        if ($bFirst)
            $this->_addContent(array("Агенство спецтехники Гусеница"));
        else
        	$this->_addContent(array($sContent));
    }
}

?>
