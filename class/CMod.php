<?php
namespace gusenru;

/**
 * CModule class
 * 
 * A page template can contain any amount of modules. They are described 
 *  by this way:
 * 
 * %{MOD_NAME&XSL_TEMPLATE&CACHE_TIME[&PARAM_1[&PARAM_2]]}%
 * 
 * If XSL_TEMPLATE is null, it means that the module will not make 
 * any XSL-transformations.
 * 
 * CACHE_TIME - duration of cache in munites.
 * If CACHE_TIME is 0, content won't be cached
 * 
 * @param CWebPage $hWebPage
 * @param string $modName
 * @param string $xslFile
 * @param1 string $param1
 * @param2 string $param2
 * 
 * @return void
 */
class CMod implements iModDriver
{
	private $_aContent = array();

    private $_modName;
	private $_param1;
	private $_param2;

    function __construct($modName, $param1, $param2) {
    	CWebPage::debug('CMod::__construct('.
    		$modName.','.
    		(empty($param1) ? '' : ','.$param1).
    		(empty($param2) ? '' : ','.$param2).
    		')'
    	);
        $this->_modName = $modName;
        $this->_param1 = $param1;
        $this->_param2 = $param2;

        switch ($this->_modName) {
        	case 'unit_page_unit':
            	$hWebPage = CWebPage::getInstance();
		    	$unit = $hWebPage->getUnit(
		    		$hWebPage->getGetValue('id')
		    	);
		    	$this->_aContent = $unit->get();
        		break;
            case "user":
                $this->userForm();
                break;
            case "unit_comments":
                $this->userComments();
                break;
            case "comments_unapproved_total":
                $this->commentsUnapprovedTotal();
                break;
            case "title":
                $this->title($this->_param1);
                break;
            case "description":
            	$this->description($this->_param1);
            	break;
            case "main_page_unit_list":
                $this->mainPageList();
                break;
            case "unit_list":
                $this->searchPageMain();
                break;
            case "search_form":
                $this->searchForm();
                break;
            case "unit_list_paginator":
                $this->searchPaginator($this->_param1);
                break;
            case 'unit_arch_list':
            	$this->unitArchList();
            	break;
            case 'unit_arch_list_paginator':
            	$this->unitArchListPaginator();
            	break;
            case "admin_unit_form":
                $this->unitForm();
                break;
            case 'owner_list':
            	$this->ownerList();
            	break;
            case 'owner_list_paginator':
            	$this->ownersPaginator();
            	break;
            case 'admin_owner_form':
            	$this->ownerForm();
            	break;
            case "admin_comments_list":
                $this->adminCommentsList();
                break;
        }
    }
    
    function __destruct () {
    	CWebPage::debug('CModule::__destruct()');
    	
        $this->_aContent = NULL;
    }
    
    private function _addContent($aContent) {
    	$this->_aContent = array_merge($this->_aContent, $aContent);
    }

    function execute() {
    	return $this->_aContent;
    }
    
    function getUserDataFromSession() {
    	$aUser = array();
    	$aUser['user']['@attributes'] = array(
    		'name' => $_SESSION["user"]["name"],
    		'type' => $_SESSION["user"]["type"],
    		'id' => $_SESSION["user"]["id"]
		);
		$aUser['user']['image'] = $_SESSION["user"]["photo"];
    	
    	$this->_addContent($aUser);
    }
    
    //------------------------------------------------------
    // Page functionality
    //------------------------------------------------------
    function commentsUnapprovedTotal() {
        $q = "SELECT count(*) AS total FROM comments WHERE approved IS NULL";
        $res = CDataBase::getInstance()->query($q);
        $row = $res->fetch(\PDO::FETCH_ASSOC);
        
        $this->_addContent(array($row['total']));
    }
    
    function title($page) {
    	$hDbConn = CDataBase::getInstance();
    	$hWebPage = CWebPage::getInstance();

        switch ($page) {
            case "page_search":
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
                break;
            case "page_unit":
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
                break;
        }
    }
    
    function description($page) {
    	$hWebPage = CWebPage::getInstance();
        switch ($page) {
            case "page_search":
		        $q = 'SELECT u.id 
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
		
		        $res = CDataBase::getInstance()->query($q);
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
		        $sDescr .= 'Спецтехника б/у, продажа от собвтенника, конкурентные цены, лизинг.';
		        
				$this->_addContent(array($sDescr));
                break;
            case "page_unit":
                if ($hWebPage->getGetValue('id') != 0) {
                	$unit = $hWebPage->getUnit(
                		$hWebPage->getGetValue('id')
                	);
                	$this->_addContent(array($unit->getDescription()));
                }
                break;
        }    	
    }
    
    function userForm() {
    	$hWebPage = CWebPage::getInstance();
    	
        if (isset($_SESSION["user"])) {
            $this->getUserDataFromSession();
            if (isset($_SESSION["user_referer"]))
                unset($_SESSION["user_referer"]);
            
            $this->_addContent(array('unit' => $hWebPage->getGetValue('id')));
        }
        else {
            list($realHost,)=explode(':',$_SERVER['HTTP_HOST']);

            $cur_link = sprintf("https://%s/%s/%d#comment_form",
                $realHost,
                $hWebPage->getGetValue('page'),
                $hWebPage->getGetValue('id')
            );

            $_SESSION["user_referer"] = $cur_link;

			// vkontakte auth url
			$vk = new \VK\VK(VK_CLIENT_ID, VK_SECRET);
			$vk->setApiVersion(VK_VERSION);
			$vkLink = $vk->getAuthorizeURL(
				'uid,first_name,last_name,sex,photo_50,email',
				sprintf("https://%s/?page=oauth_vk", $realHost)
			);

			// facebook auth url
			$fb = new \Facebook\Facebook([
			  'app_id'					=> FB_CLIENT_ID,
			  'app_secret'				=> FB_SECRET,
			  'default_graph_version'	=> FB_VERSION
			  ]);
			
			$helper = $fb->getRedirectLoginHelper();
			
			$permissions = ['public_profile, email'];
			$fbLink = $helper->getLoginUrl(
				sprintf("https://%s/?page=oauth_fb", $realHost), 
				$permissions
			);

			// google auth url
			$client = new \Google_Client();
			$client->setClientId(GL_CLIENT_ID);
			$client->setClientSecret(GL_SECRET);
			$client->setRedirectUri(sprintf("https://%s/?page=oauth_gl", 
				$realHost));
			
			$client->setScopes(array(
				'https://www.googleapis.com/auth/userinfo.email',
				'https://www.googleapis.com/auth/userinfo.profile')
			);
			
			$glLink = $client->createAuthUrl();

            // login form
            $aSocialList = array();
            $aSocialList['snetwork1']['link'] = $vkLink;
            $aSocialList['snetwork1']['@attributes']['type'] = 'vk';
            $aSocialList['snetwork2']['link'] = $glLink;
            $aSocialList['snetwork2']['@attributes']['type'] = 'gl';
            $aSocialList['snetwork3']['link'] = $fbLink;
            $aSocialList['snetwork3']['@attributes']['type'] = 'fb';

            $this->_addContent($aSocialList);
        }
    }

    /**
     * Makes an XML with unapproved comments:
     * 
     * <?xml version="1.0" encoding="utf-8"?>
     * <root>
     *  <comment id="COMMENT_ID" unit_id="UNIT_ID">
     *      COMMENT_TEXT
     *  </comment>
     *  <comment id="COMMENT_ID" unit_id="UNIT_ID">
     *      COMMENT_TEXT
     *  </comment>
     * </root>
     * 
     * @return void
     * 
     */
    function adminCommentsList() {
    	$hDbConn = CDataBase::getInstance();
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
            
            CWebPage::getInstance()->resetCache();
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
    
    function ownerList() {
    	$hDbConn = CDataBase::getInstance();
    	$hWebPage = CWebPage::getInstance();
    	
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
    
    function ownersPaginator() {
    	$hWebPage = CWebPage::getInstance();
        $q = '
        	SELECT
			COUNT(*) AS total
			FROM owners';

        $countRes = CDataBase::getInstance()->query($q);
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
        
        $oPaginator = new CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );

		$this->_addContent($oPaginator->get());
    }
    
    function searchForm() {
    	$hDbConn = CDataBase::getInstance();
    	$hWebPage = CWebPage::getInstance();

		$aSearchForm = array();
		$aSearchForm['page'] = $this->_param1;
        
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
    
    /**
     * Generates XML-data for unit form content. It will be combined with XSL 
     * file to get empty form for unit adding or filled form for unit editing.
     * 
     * @return void
     */
    function unitForm() {
    	$hDbConn = CDataBase::getInstance();
    	$hWebPage = CWebPage::getInstance();
    	
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
    
    function ownerForm() {
    	$hWebPage = CWebPage::getInstance();
    	
    	$aForm = array();
    	
        if ($hWebPage->getGetValue('id')) {
        	$hDbConn = CDataBase::getInstance();
        	
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

    /**
     * Generates XML data with unit comments for
     * specified unit page
     * 
     * <?xml version="1.0" encoding="utf-8"?>
     * <root>
     *  <unit_id>43</unit_id>
     *  <comments>
     *      <comment id="COMMENT_ID" user_id="USER_ID" 
     *                  type="COMMENT_TYPE" approved="IS_APPROVED">
     *          COMMENT_TEXT
     *          <comment id="COMMENT_ID" user_id="USER_ID"
     *                  type="COMMENT_TYPE" approved="IS_APPROVED">
     *              COMMENT_TEXT
     *          </comment>
     *      </comment>
     *  </comments>
     * </root>
     */
    function userComments() {
    	$hDbConn = CDataBase::getInstance();
    	$hWebPage = CWebPage::getInstance();
    	
        // fill user login form
        if (isset($_SESSION["user"])) {
            $this->getUserDataFromSession();
        }        

		$aComments = array();
		$aComments['unit_id'] = $hWebPage->getGetValue('id');
		
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

    function searchPaginator($page) {
    	$hWebPage = CWebPage::getInstance();
    	
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

        $countRes = CDataBase::getInstance()->query($q);
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
            	$aVar = ['search', $vType, $vManuf, $vFedDistr, '%d'];
            	$sLinkPattern = '/'.implode("/", $aVar);
                break;
            case "admin":
            	$aVar = array(
            		'page'		=> $hWebPage->getGetValue('page'),
            		'act'		=> $hWebPage->getGetValue('act'),
            		'vType'		=> $vType,
            		'vManut'	=> $vManuf,
            		'vFedDistr'	=> $vFedDistr,
            		'offset'	=> '%d'
            	);
            	$sLinkPattern = '?'.http_build_query($aVar);
                break;
        }
        
        $oPaginator = new CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );
        
        $this->_addContent($oPaginator->get());
    }
    
    function unitArchList() {
    	$hWebPage = CWebPage::getInstance();
    	
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

        $aRes = CDataBase::getInstance()
        	->query($q)
        	->fetchAll(\PDO::FETCH_ASSOC);
        for ($i=0;$i<count($aRes);$i++) {
        	$unit = $hWebPage->getUnit($aRes[$i]['id']);
        	$aArch["unit{$i}"] = $unit->get()['unit'];
        	unset($unit);
        };
        
        $this->_addContent($aArch);
	}
	
	function unitArchListPaginator() {
		$hWebPage = CWebPage::getInstance();
        $q = '
        	SELECT
			COUNT(*) AS total
    		FROM units u
    		WHERE is_arch=TRUE';
        		
        $countRes = CDataBase::getInstance()->query($q);
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
        
        $oPaginator = new CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );
        
        $this->_addContent($oPaginator->get());
	}
    
    function searchPageMain() {
    	$hWebPage = CWebPage::getInstance();
    	
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

        $aRes = CDataBase::getInstance()->query($q)->fetchAll(\PDO::FETCH_ASSOC);
        
        for ($i=0;$i<count($aRes);$i++) {
        	$unit = $hWebPage->getUnit($aRes[$i]['id']);
        	$aUnit["unit{$i}"] = $unit->get()['unit'];
        	unset($unit);
        }
        
        $this->_addContent($aUnit);
    }

    function mainPageList() {
    	$hDbConn = CDataBase::getInstance();
    	
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
			
	        $stmt->execute();
	        $aRes2 = $stmt->fetchAll(\PDO::FETCH_ASSOC);    	

			for ($x=0;$x<count($aRes2);$x++) {
            	$unit = CWebPage::getInstance()->getUnit($aRes2[$x]['id']);
            	$aCategory["category{$i}"]["unit{$x}"] = $unit->get()['unit'];
				unset($unit);
			}
			unset($aRes2);
		}
		
		$this->_addContent($aCategory);
    }
        
}

?>
