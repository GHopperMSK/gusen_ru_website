<?php
namespace gusenru\module;

class CUnitMod extends \gusenru\CMod
{
    function __construct($param1, $param2) {
    	parent::__construct($param1, $param2);

        switch ($this->_param1) {
            case "main_page":
                $this->_mainPageList();
                break;
            case "search_page":
                $this->_searchPageMain();
                break;
        	case 'unit_page':
            	$hWebPage = \gusenru\CWebPage::getInstance();
		    	$unit = $hWebPage->getUnit(
		    		$hWebPage->getGetValue('id')
		    	);
		    	$this->_aContent = $unit->get();
        		break;
            case 'arch_page':
            	$this->_unitArchList();
            	break;
            case "unapproved_comments_page":
                $this->_unapprovedCommentsList();
                break;
            case "comments":
                $this->_unitComments();
                break;
            case "unapproved_count":
                $this->_commentsUnapprovedTotal();
                break;
            case "unit_form":
                $this->_unitForm();
                break;
        }
    }

    private function _mainPageList() {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	
    	$aCategory = array();
    	
    	$stmt = $hDbConn->prepare('
        	SELECT 
    			cat.id,
    			cat.name
    		FROM 
    			categories cat 
    		JOIN units u ON cat.id=u.cat_id 
    		WHERE u.is_arch = FALSE
    		GROUP BY cat.name 
    		HAVING COUNT(cat.id)>1'
	    );
        $stmt->execute();
        $aRes = $stmt->fetchAll(\PDO::FETCH_ASSOC);    	

    	$stmt = $hDbConn->prepare('
			SELECT id
			FROM units
			WHERE
				is_arch=FALSE AND
				cat_id=:cat_id
			ORDER BY date DESC
			LIMIT 4'
	    );
	    $stmt->bindParam(':cat_id', $cat_id, \PDO::PARAM_INT);

		for ($i=0;$i<count($aRes);$i++) {
			$cat_id = $aRes[$i]['id'];
			
			$aCategory["category{$i}"]['@attributes'] = array(
				'id' => $aRes[$i]['id'],
				'name' => $aRes[$i]['name']
			);

			$aCategory["category{$i}"]['link'] = MOD_REWRITE
				? "/search/{$aRes[$i]['id']}"
				: "/?page=search&vType={$aRes[$i]['id']}";

	        $stmt->execute();
	        $aRes2 = $stmt->fetchAll(\PDO::FETCH_ASSOC);    	

			for ($x=0;$x<count($aRes2);$x++) {
            	$unit = \gusenru\CWebPage::getInstance()->getUnit($aRes2[$x]['id']);
            	$aCategory["category{$i}"]["unit{$x}"] = $unit->get()['unit'];
				unset($unit);
			}
			unset($aRes2);
		}
		
		$this->_addContent($aCategory);
    }

    private function _searchPageMain() {
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
    	$aUnit = array();

        $q = 'SELECT u.id 
        		FROM units u
				JOIN cities ON u.city_id=cities.id
				JOIN regions ON cities.rd_id=regions.id
        		WHERE is_arch=FALSE';

        if ($hWebPage->getGetValue('vType')) {
            $q .= sprintf(" AND cat_id=%d",
            	$hWebPage->getGetValue('vType'));
        }
        if ($hWebPage->getGetValue('vManuf')) {
            $q .= sprintf(" AND manufacturer_id=%d", 
            	$hWebPage->getGetValue(vManuf));
        }
        if ($hWebPage->getGetValue('vFedDistr')) {
            $q .= sprintf(" AND fd_id=%d", 
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

        $aRes = \gusenru\CDataBase::getInstance()->query($q)->fetchAll(\PDO::FETCH_ASSOC);
        
        for ($i=0;$i<count($aRes);$i++) {
        	$unit = $hWebPage->getUnit($aRes[$i]['id']);
        	$aUnit["unit{$i}"] = $unit->get()['unit'];
        	unset($unit);
        }
        
        $this->_addContent($aUnit);
    }

    function _unitArchList() {
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
    	$aArch = array();
    	
        $q = '
        	SELECT id 
    		FROM units
    		WHERE is_arch=TRUE
    		ORDER BY date DESC LIMIT %d,%d';

        if ($hWebPage->getGetValue('offset')) {
            $iOffset = max($hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }        
        
        $q = sprintf($q,
            ($iOffset-1)*PAGINATOR_SHOW_ON_PAGE,
            PAGINATOR_SHOW_ON_PAGE
        );

        $aRes = \gusenru\CDataBase::getInstance()
        	->query($q)
        	->fetchAll(\PDO::FETCH_ASSOC);
        for ($i=0;$i<count($aRes);$i++) {
        	$unit = $hWebPage->getUnit($aRes[$i]['id']);
        	$aArch["unit{$i}"] = $unit->get()['unit'];
        	unset($unit);
        };
        
        $this->_addContent($aArch);
	}

    function _unapprovedCommentsList() {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	$aComments = array();
    	
        if (isset($_POST['comment_id'])) {
            $stmt = $hDbConn->prepare('
            	UPDATE comments
            	SET approved=:approve 
            	WHERE id=:id'
            );
            $stmt->bindParam(':approve', $approve, \PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $com_id, \PDO::PARAM_INT);
            foreach ($_POST['comment_id'] as $com_id) {
                $approve = in_array($com_id, $_POST['approved']) ? TRUE : FALSE;
                $stmt->execute();
            }
            
            \gusenru\CWebPage::getInstance()->resetCache();
        }
        $q = '
        	SELECT cm.id,cm.comment,un.id AS unit_id 
        	FROM comments cm
            JOIN units un ON cm.unit_id=un.id
            WHERE approved IS NULL
            ORDER BY cm.date ASC 
            LIMIT 200';

		$aRes = $hDbConn->query($q)->fetchAll(\PDO::FETCH_ASSOC);
		
		for ($i=0;$i<count($aRes);$i++) {
			$aComments['comments']["comment{$i}"]['@content'] =
				$aRes[$i]['comment'];
			$aComments['comments']["comment{$i}"]['@attributes'] = array(
				'id' => $aRes[$i]['id'],
				'unit_id' => $aRes[$i]['unit_id']
			);
		}

		$this->_addContent($aComments);
    }

    private function _unitComments() {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
		$aComments = array();
		
        // fill comments list
        $stmt = $hDbConn->prepare('
        	SELECT *
        	FROM (
	    		SELECT 
	        		id,
	        		user_id,
	        		type,
	        		name,
	        		comment,
	        		CASE approved
	        			WHEN FALSE THEN FALSE
	        			ELSE TRUE
	        		END AS approved,
	        		date
	    		FROM comments
	        	WHERE 
	        		unit_id=:unit_id AND
	        		p_com_id IS NULL 
	        	ORDER BY date DESC
	        	LIMIT 40) tmp
	        ORDER BY tmp.date'
	    );
        $stmt->bindValue(
        	':unit_id',
        	$hWebPage->getGetValue('id'),
        	\PDO::PARAM_INT
        );
        $stmt->execute();
        $aRes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $hDbConn->prepare('
        	SELECT *
        	FROM (
	    		SELECT 
	        		id,
	        		user_id,
	        		type,
	        		name,
	        		comment,
	        		CASE approved
	        			WHEN FALSE THEN FALSE
	        			ELSE TRUE
	        		END AS approved,
	        		date
	    		FROM comments
	        	WHERE 
	        		unit_id=:unit_id AND
	        		p_com_id=:p_com_id 
	        	ORDER BY date DESC
	        	LIMIT 20) tmp
	        ORDER BY tmp.date'
	    );
        $stmt->bindValue(
        	':unit_id',
        	$hWebPage->getGetValue('id'),
        	\PDO::PARAM_INT
        );
	    $stmt->bindParam(':p_com_id', $p_com_id, \PDO::PARAM_INT);

        for ($i=0;$i<count($aRes);$i++) {
        	$p_com_id = $aRes[$i]['id'];

			$aComments["comments"]["comment{$i}"]['text'] = $aRes[$i]['comment'];
        	$aComments["comments"]["comment{$i}"]['@attributes'] = array(
        		'name' => $aRes[$i]['name'],
        		'id' => $aRes[$i]['id'],
        		'user_id' => $aRes[$i]['user_id'],
        		'type' => $aRes[$i]['type'],
        		'approved' => ($aRes[$i]['approved']) ?
        			'TRUE' : 'FALSE'
    		);
    		
	        $stmt->execute();
	        $aRes2 = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	        for ($x=0;$x<count($aRes2);$x++) {
	        	$aComments["comments"]["comment{$i}"]
	        			["comment{$x}"]['@content'] = 
	        		$aRes2[$x]['comment'];
	
	        	$aComments["comments"]["comment{$i}"]
	        			["comment{$x}"]['@attributes'] = array(
	        		'name' => $aRes2[$x]['name'],
	        		'id' => $aRes2[$x]['id'],
	        		'user_id' => $aRes2[$x]['user_id'],
	        		'type' => $aRes2[$x]['type'],
	        		'approved' => ($aRes2[$x]['approved']) ? 
	        			'TRUE' : 'FALSE'
	    		);
	        	
	        }
	        unset($aRes2);
        }

        $this->_addContent($aComments);
    }

    function _unitForm() {
    	$hDbConn = \gusenru\CDataBase::getInstance();
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
    	$aForm = array();
    	
        // is it a new unit or editing existing one
        if ($hWebPage->getGetValue('id')) {
            $isEdit = TRUE;
            
            $unit = $hWebPage->getUnit(
            	$hWebPage->getGetValue('id')
            );
            
            $aForm['id'] = $hWebPage->getGetValue('id');
            $actType = "unit_edit";            
        }
        else {
            $isEdit = false;
            $actType = "unit_add";
        }

		$aForm['act'] = $actType;
        $q = "SELECT id,name FROM owners ORDER BY name";
        $aRes = $hDbConn->query($q)->fetchAll(\PDO::FETCH_ASSOC);
        for ($i=0;$i<count($aRes);$i++) {
        	$aForm['owners']["owner{$i}"]['@content'] = $aRes[$i]['name'];
        	$aForm['owners']["owner{$i}"]['@attributes']['id'] =
        		$aRes[$i]['id'];
            if (($isEdit) && ($unit->owner_id === $aRes[$i]['id'])) {
                $aForm['owners']["owner{$i}"]['@attributes']['selected'] = 
                	'TRUE'; 
            }
        };
        $q = "SELECT id,name FROM categories ORDER BY name";
        $aRes = $hDbConn->query($q)->fetchAll(\PDO::FETCH_ASSOC);
        for ($i=0;$i<count($aRes);$i++) {
        	$aForm['categories']["category{$i}"]['@content'] =
        		$aRes[$i]['name'];
        	$aForm['categories']["category{$i}"]['@attributes']['id'] =
        		$aRes[$i]['id'];
            if (($isEdit) && ($unit->cat_id === $aRes[$i]['id'])) {
                $aForm['categories']["category{$i}"]
                	['@attributes']['selected'] = 'TRUE'; 
            }
        }

        $q = "SELECT id,name FROM fdistricts ORDER BY name";
        $aRes = $hDbConn->query($q)->fetchAll(\PDO::FETCH_ASSOC);
        for ($i=0;$i<count($aRes);$i++) {
        	$aForm['fdistricts']["fdistrict{$i}"]['@content'] =
        		$aRes[$i]['name'];
        	$aForm['fdistricts']["fdistrict{$i}"]['@attributes']['id'] =
        		$aRes[$i]['id'];
            if (($isEdit) &&
            		($unit->getCityParam('fdist_id') === $aRes[$i]['id'])) {
                $aForm['fdistricts']["fdistrict{$i}"]
                	['@attributes']['selected'] = 'TRUE'; 
            }
        }

        $q = "SELECT id,name FROM manufacturers ORDER BY name";
        $aRes = $hDbConn->query($q)->fetchAll(\PDO::FETCH_ASSOC);
        for ($i=0;$i<count($aRes);$i++) {
        	$aForm['manufacturers']["manufacturer{$i}"]['@content'] =
        		$aRes[$i]['name'];
        	$aForm['manufacturers']["manufacturer{$i}"]['@attributes']['id'] =
        		$aRes[$i]['id'];
            if (($isEdit) && ($unit->manuf_id === $aRes[$i]['id'])) {
                $aForm['manufacturers']["manufacturer{$i}"]
                	['@attributes']['selected'] = 'TRUE'; 
            }
        }

        if ($isEdit) {
	        $q = sprintf("SELECT id,name FROM cities WHERE rd_id=%d",
	            $unit->getCityParam('reg_id'));
	        $aRes = $hDbConn->query($q)->fetchAll(\PDO::FETCH_ASSOC);
	        for ($i=0;$i<count($aRes);$i++) {
	        	$aForm['cities']["city{$i}"]['@content'] = $aRes[$i]['name'];
	        	$aForm['cities']["city{$i}"]['@attributes']['id'] =
	        		$aRes[$i]['id'];
	            if (($isEdit) &&
	            		($unit->getCityParam('id') === $aRes[$i]['id'])) {
	                $aForm['cities']["city{$i}"]
	                	['@attributes']['selected'] = 'TRUE'; 
	            }
	        }
			$aForm['name'] = $unit->name;
			$aForm['description'] = $unit->description;
			$aForm['year'] = $unit->year;
			$aForm['price'] = $unit->price;
			if (isset($unit->mileage)) {
				$aForm['mileage'] = $unit->mileage;
			}
			if (isset($unit->op_time)) {
				$aForm['op_time'] = $unit->op_time;
			}
			$aImg = $unit->img;
			for($i=0;$i<count($aImg);$i++) {
				$aForm['images']["image{$i}"] = $aImg[$i];
			}
        }
        
        $this->_addContent($aForm);
    }

    private function _commentsUnapprovedTotal() {
        $q = "SELECT count(*) AS total FROM comments WHERE approved IS NULL";
        $res = \gusenru\CDataBase::getInstance()->query($q);
        $row = $res->fetch(\PDO::FETCH_ASSOC);
        
        $this->_addContent(array($row['total']));
    }
}

?>
