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
class CModule
{
    protected $xslDoc;
    protected $xmlDoc;
    protected $hWebPage;

    function __construct(CWebPage $hWebPage, $modName, $xslFile, $param1, $param2) {
    	CWebPage::debug('CModule::__construct(CDataBase,'.
    		$modName.','.
    		$xslFile.
    		(isset($param1) ? ','.$para1 : '').
    		(isset($param2) ? ','.$param2 : '').
    		')'
    	);
    	
        $this->hWebPage = $hWebPage;
        $this->modName = $modName;
        $this->xslFile = $xslFile;
        $this->param1 = $param1;
        $this->param2 = $param2;
        
        $this->content = '';
        $this->hDbConn = $this->hWebPage->getDataBaseHandler();
        
        if ($this->xslFile != 'null') {
            if (file_exists("xsl/".$this->xslFile)) {
                $this->xslDoc = new \DOMDocument();
                $this->xslDoc->load("xsl/".$this->xslFile);
    
                $this->xmlDoc = new \DOMDocument('1.0', 'utf-8');
                $eRoot = $this->xmlDoc->createElement('root');
                $this->eRoot = $this->xmlDoc->appendChild($eRoot);
            }
            else
                throw new \Exception('XSL file haven\'t found!');
        }

        switch ($this->modName) {
            case "main_page_unit_list":
                $this->mainPageList();
                break;
            case "unit_page_unit":
		    	$unit = $this->hWebPage->getUnit(
		    		$this->hWebPage->getGetValue('id')
		    	);
		    	$unit = $this->xmlDoc->importNode($unit->getUnitDOM(), true);
				$this->eRoot->appendChild($unit);
                break;
            case "unit_list":
                $this->searchPageMain();
                break;
            case 'unit_arch_list':
            	$this->unitArchList();
            	break;
            case 'unit_arch_list_paginator':
            	$this->unitArchListPaginator();
            	break;
            case "unit_list_paginator":
                $this->searchPaginator($param1);
                break;
            case "user":
                $this->userForm();
                break;
            case "unit_comments":
                $this->userComments();
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
            case "search_form":
                $this->searchForm();
                break;
            case "comments_unapproved_total":
                $this->commentsUnapprovedTotal();
                break;
            case "admin_comments_list":
                $this->adminCommentsList();
                break;
            case "title":
                $this->title($this->param1);
                break;
            case "description":
            	$this->description($this->param1);
            	break;
        }
    }
    
    function __destruct () {
    	CWebPage::debug('CModule::__destruct()');
        unset($this->xslDoc);
        unset($this->xmlDoc);
    }  
    
    function fillUser() {
        $sUser = $this->xmlDoc->createElement("user");
        $sUser = $this->eRoot->appendChild($sUser);
        $sUserAttr = $this->xmlDoc->createAttribute('name');
        $sUserAttr->value = $_SESSION["user"]["name"];
        $sUser->appendChild($sUserAttr);
        $sUserAttr = $this->xmlDoc->createAttribute('type');
        $sUserAttr->value = $_SESSION["user"]["type"];
        $sUser->appendChild($sUserAttr);
        $sUserAttr = $this->xmlDoc->createAttribute('id');
        $sUserAttr->value = $_SESSION["user"]["id"];
        $sUser->appendChild($sUserAttr);
        
        $sUserData = $this->xmlDoc->createElement("img", htmlentities($_SESSION["user"]["photo"]));
        $sUser->appendChild($sUserData);

        $this->eRoot->appendChild($sUser);
    }
    
    function execute() {
        if ($this->xslFile != 'null') {
            $hProc = new \XSLTProcessor();
            $hProc->importStylesheet($this->xslDoc);
            // clear all whitespaces from result
		    $sModContent = preg_replace(
		    	'/\s{2,}/',
		    	' ',
		    	$hProc->transformToXML($this->xmlDoc)
		    );
            return $sModContent;
        }
        else {
            return $this->content;
        }
    }

    //------------------------------------------------------
    // Page functionality
    //------------------------------------------------------
    
    function userForm() {
        if (isset($_SESSION["user"])) {
            $this->fillUser();
            if (isset($_SESSION["user_referer"]))
                unset($_SESSION["user_referer"]);
	    	
	        $eUnit = $this->xmlDoc->createElement(
	        	'unit',
	        	$this->hWebPage->getGetValue('id')
	        );
	        $eUnit = $this->eRoot->appendChild($eUnit);
            $this->eRoot->appendChild($eUnit);
        }
        else {
            list($realHost,)=explode(':',$_SERVER['HTTP_HOST']);

            $cur_link = sprintf("https://%s/%s/%d#comment_form",
                $realHost,
                $this->hWebPage->getGetValue('page'),
                $this->hWebPage->getGetValue('id')
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
            $sNet = $this->xmlDoc->createElement("snetwork");
            $sNet = $this->eRoot->appendChild($sNet);
            $subNodeAttr = $this->xmlDoc->createAttribute('type');
            $subNodeAttr->value = 'vk'; 
            $sNet->appendChild($subNodeAttr);
            $sNetLink = $this->xmlDoc->createElement("link",
            	htmlspecialchars($vkLink));
            $sNet->appendChild($sNetLink);
            $sNetLogo = $this->xmlDoc->createElement("img", "LOGO_ADDR");
            $sNet->appendChild($sNetLogo);

            $sNet = $this->xmlDoc->createElement("snetwork");
            $sNet = $this->eRoot->appendChild($sNet);
            $subNodeAttr = $this->xmlDoc->createAttribute('type');
            $subNodeAttr->value = 'fb'; 
            $sNet->appendChild($subNodeAttr);
            $sNetLink = $this->xmlDoc->createElement("link", 
            	htmlspecialchars($fbLink));
            $sNet->appendChild($sNetLink);
            $sNetLogo = $this->xmlDoc->createElement("img", "LOGO_ADDR");
            $sNet->appendChild($sNetLogo);

            $sNet = $this->xmlDoc->createElement("snetwork");
            $sNet = $this->eRoot->appendChild($sNet);
            $subNodeAttr = $this->xmlDoc->createAttribute('type');
            $subNodeAttr->value = 'gl'; 
            $sNet->appendChild($subNodeAttr);
            $sNetLink = $this->xmlDoc->createElement("link",
            	htmlspecialchars($glLink));
            $sNet->appendChild($sNetLink);
            $sNetLogo = $this->xmlDoc->createElement("img", "LOGO_ADDR");
            $sNet->appendChild($sNetLogo);

            $this->eRoot->appendChild($sNet);          
        }
    }
    
    function commentsUnapprovedTotal() {
        $q = "SELECT count(*) AS total FROM comments WHERE approved IS NULL";
        $res = $this->hDbConn->query($q);
        $row = $res->fetch(\PDO::FETCH_ASSOC);
        
        $this->content = $row['total'];
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
        if (isset($_POST['comment_id'])) {
            $stmt = $this->hDbConn->prepare('
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
            
            $this->hWebPage->resetCache();
        }
        $q = '
        	SELECT cm.id,cm.comment,un.id AS unit_id 
        	FROM comments cm
            JOIN units un ON cm.unit_id=un.id
            WHERE approved IS NULL
            ORDER BY cm.date ASC 
            LIMIT 200';
        $res = $this->hDbConn->query($q);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            $sComent = $this->xmlDoc->createElement("comment",
                htmlentities($row['comment']));
            $sComent = $this->eRoot->appendChild($sComent);
            $attr = $this->xmlDoc->createAttribute('id');
            $attr->value = $row['id'];
            $sComent->appendChild($attr);
            $attr = $this->xmlDoc->createAttribute('unit_id');
            $attr->value = $row['unit_id'];
            $sComent->appendChild($attr);
        }
    }
    
    function ownerList() {
    	// TODO: pagination
        $q = '
        	SELECT id,name,description
        	FROM owners
        	ORDER BY name';
        	
        if ($this->hWebPage->getGetValue('offset')) {
            $iOffset = max($this->hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }        
        
        $q .= sprintf(" LIMIT %d,%d",
            ($iOffset-1)*PAGINATOR_SHOW_ON_PAGE,
            PAGINATOR_SHOW_ON_PAGE
        );

        $res = $this->hDbConn->query($q);
        while ($ur = $res->fetch(\PDO::FETCH_ASSOC)) {
        	$unit = $this->hWebPage->getUnit($ur['id']);
        	$unit = $unit->getUnitDOM();
			$unit = $this->xmlDoc->importNode($unit, true);
			$this->eRoot->appendChild($unit);
			unset($unit);
        }        	
        	
        $res = $this->hDbConn->query($q);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            $sOwner = $this->xmlDoc->createElement("owner",
                htmlentities($row['description']));
            $sOwner = $this->eRoot->appendChild($sOwner);
            $attr = $this->xmlDoc->createAttribute('id');
            $attr->value = $row['id'];
            $sOwner->appendChild($attr);
            $attr = $this->xmlDoc->createAttribute('name');
            $attr->value = $row['name'];
            $sOwner->appendChild($attr);
        }
    }
    
    function ownersPaginator() {
        $q = '
        	SELECT
			COUNT(*) AS total
			FROM owners';

        $countRes = $this->hDbConn->query($q);
        $iTotal = $countRes->fetch(\PDO::FETCH_ASSOC)['total'];

        // current page number
        if ($this->hWebPage->getGetValue('offset')) {
            $iOffset = max($this->hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }
        
    	$aVar = array(
    		'page'		=> $this->hWebPage->getGetValue('page'),
    		'act'		=> $this->hWebPage->getGetValue('act'),
    		'offset'	=> '%d'
    	);
    	$sLinkPattern = htmlentities('?'.http_build_query($aVar));
        
        $oPaginator = new CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );
        
		$this->eRoot->appendChild(
		    $this->xmlDoc->importNode(
		        $oPaginator->getXML(),
		        TRUE
		    )
		);    	
    }
    
    function searchForm() {
        $xmlTop = $this->xmlDoc->createElement("page", $this->param1);
        $xmlTop = $this->eRoot->appendChild($xmlTop);
        
        $xmlTop = $this->xmlDoc->createElement("categories");
        $xmlTop = $this->eRoot->appendChild($xmlTop);
        $q = '
        	SELECT cat.id,cat.name
        	FROM categories cat
            JOIN units u ON cat.id=u.cat_id
            GROUP BY cat.id HAVING count(cat.id) > 0
            ORDER BY name';
        $res = $this->hDbConn->query($q);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            $xmlSubTop = $this->xmlDoc->createElement("category",
                htmlentities($row['name']));
            $xmlSubTopAttr = $this->xmlDoc->createAttribute('id');
            $xmlSubTopAttr->value = $row['id']; 
            $xmlSubTop->appendChild($xmlSubTopAttr);
            if ($this->hWebPage->getGetValue('vType') && 
            		$this->hWebPage->getGetValue('vType') == $row['id']) {
                $xmlSubTopAttr = $this->xmlDoc->createAttribute('selected');
                $xmlSubTopAttr->value = 'true';                 
                $xmlSubTop->appendChild($xmlSubTopAttr);
            }
            $xmlTop->appendChild($xmlSubTop);
        }
        $xmlTop = $this->xmlDoc->createElement("manufacturers");
        $xmlTop = $this->eRoot->appendChild($xmlTop);
        $q = '
        	SELECT
    			m.id,
    			m.name
    		FROM manufacturers m
    		JOIN units u ON m.id=u.manufacturer_id
    		GROUP BY m.id 
    		HAVING count(m.id)>0 
    		ORDER BY name';
        $res = $this->hDbConn->query($q);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            $xmlSubTop = $this->xmlDoc->createElement("manufacturer",
                htmlentities($row['name']));
            $xmlSubTopAttr = $this->xmlDoc->createAttribute('id');
            $xmlSubTopAttr->value = $row['id']; 
            $xmlSubTop->appendChild($xmlSubTopAttr);
            if ($this->hWebPage->getGetValue('vManuf') &&
            		$this->hWebPage->getGetValue('vManuf') == $row['id']) {
                $xmlSubTopAttr = $this->xmlDoc->createAttribute('selected');
                $xmlSubTopAttr->value = 'true';                 
                $xmlSubTop->appendChild($xmlSubTopAttr);
            }
            $xmlTop->appendChild($xmlSubTop);
        }
        $xmlTop = $this->xmlDoc->createElement("fdistricts");
        $xmlTop = $this->eRoot->appendChild($xmlTop);
        $q = '
        	SELECT f.id,f.name
        	FROM fdistricts f 
        	LEFT JOIN regions r ON r.fd_id=f.id 
        	LEFT JOIN cities c ON c.rd_id=r.id
        	LEFT JOIN units u ON u.city_id=c.id
        	GROUP BY f.id
        	HAVING COUNT(u.id)>0;';
        $res = $this->hDbConn->query($q);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            $xmlSubTop = $this->xmlDoc->createElement("fdistrict",
                htmlentities($row['name']));
            $xmlSubTopAttr = $this->xmlDoc->createAttribute('id');
            $xmlSubTopAttr->value = $row['id']; 
            $xmlSubTop->appendChild($xmlSubTopAttr);
            if ($this->hWebPage->getGetValue('vFedDistr') && 
            		$this->hWebPage->getGetValue('vFedDistr') == $row['id']) {
                $xmlSubTopAttr = $this->xmlDoc->createAttribute('selected');
                $xmlSubTopAttr->value = 'true';                 
                $xmlSubTop->appendChild($xmlSubTopAttr);
            }
            $xmlTop->appendChild($xmlSubTop);
        }
    
    }
    
    function title($page) {
        switch ($page) {
            case "page_search":
                $bFirst = true;

                if ($this->hWebPage->getGetValue('vType') > 0) {
                    $stmt = $this->hDbConn->prepare('
                    	SELECT name 
						FROM categories 
						WHERE id=:id'
					);
                    $stmt->bindValue(':id',
                    	$this->hWebPage->getGetValue('vType'),
                    	\PDO::PARAM_INT
                    );
                    $stmt->execute();
                    $this->content = $stmt->fetch(\PDO::FETCH_ASSOC)['name'];
                    $bFirst = false;
                }
                if ($this->hWebPage->getGetValue('vManuf') > 0) {
                    $stmt = $this->hDbConn->prepare('
                    	SELECT name
						FROM manufacturers
						WHERE id=:id'
					);
                    $stmt->bindValue(':id',
                    	$this->hWebPage->getGetValue('vManuf'),
                    	\PDO::PARAM_INT
                    );
                    $stmt->execute();
                    if (!$bFirst)
                        $this->content .= ' / ';
                    else
                        $bFirst = false;
                    $this->content .= $stmt->fetch(\PDO::FETCH_ASSOC)['name'];
                }
                if ($this->hWebPage->getGetValue('vFedDistr') > 0) {
                    $stmt = $this->hDbConn->prepare('
                    	SELECT name 
						FROM fdistricts 
						WHERE id=:id'
					);
                    $stmt->bindValue(':id',
                    	$this->hWebPage->getGetValue('vFedDistr'),
                    	\PDO::PARAM_INT
                    );
                    $stmt->execute();
                    if (!$bFirst)
                        $this->content .= ' / ';
                    else
                        $bFirst = false;
                    $this->content .= $stmt->fetch(\PDO::FETCH_ASSOC)['name'];
                }    
                if ($bFirst)
                    $this->content = "Агенство спецтехники Гусеница";
                break;
            case "page_unit":
                if ($this->hWebPage->getGetValue('id')) {
                    $q = sprintf('
                    	SELECT
        				CONCAT(m.name,\' \', u.name) as name 
            			FROM units u
            			JOIN manufacturers m
            				ON u.manufacturer_id=m.id
            			WHERE u.id=%d',
                        $this->hWebPage->getGetValue('id'));
                    $res = $this->hDbConn->query($q);
                    $this->content = $res->fetch(\PDO::FETCH_ASSOC)['name'];
                }
                break;
        }
    }
    
    function description($page) {
        switch ($page) {
            case "page_search":
		        $q = 'SELECT u.id 
		        		FROM units u
						JOIN cities c ON u.city_id=c.id
						JOIN regions r ON c.rd_id=r.id
		        		WHERE is_arch=FALSE';
		
		        if ($this->hWebPage->getGetValue('vType') != 0) {
		            $q .= sprintf(" AND u.cat_id=%d",
		            	$this->hWebPage->getGetValue('vType'));
		        }
		        if ($this->hWebPage->getGetValue('vManuf')) {
		            $q .= sprintf(" AND u.manufacturer_id=%d",
		            	$this->hWebPage->getGetValue(vManuf));
		        }
		        if ($this->hWebPage->getGetValue('vFedDistr')) {
		            $q .= sprintf(" AND r.fd_id=%d",
		            	$this->hWebPage->getGetValue(vFedDistr));
		        }
		
		        if ($this->hWebPage->getGetValue('offset')) {
		            $iOffset = max($this->hWebPage->getGetValue('offset'), 1);
		        } else {
		            $iOffset = 1;
		        }        
		        
		        $q .= sprintf(" ORDER BY date DESC LIMIT %d,%d",
		            ($iOffset-1)*PAGINATOR_SHOW_ON_PAGE,
		            PAGINATOR_SHOW_ON_PAGE
		        );
		
		        $res = $this->hDbConn->query($q);
		        $aCategories = array();
		        $aManufacturers = array();
		        while ($ur = $res->fetch(\PDO::FETCH_ASSOC)) {
		        	$unit = $this->hWebPage->getUnit($ur['id']);
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
		        
				$this->content = $sDescr;
                break;
            case "page_unit":
                if ($this->hWebPage->getGetValue('id') != 0) {
                	$unit = $this->hWebPage->getUnit(
                		$this->hWebPage->getGetValue('id')
                	);
                	$this->content = $unit->getDescription();
                }
                break;
        }    	
    }

    /**
     * Generates XML-data for unit form content. It will be combined with XSL 
     * file to get empty form for unit adding or filled form for unit editing.
     * 
     * @return void
     */
    function unitForm() {        
        // is it a new unit or editing existing one
        if ($this->hWebPage->getGetValue('id')) {
            $isEdit = true;
            
            $unit = $this->hWebPage->getUnit(
            	$this->hWebPage->getGetValue('id')
            );
            $xmlTop = $this->xmlDoc->createElement("id", 
                htmlentities($this->hWebPage->getGetValue('id')));
            $xmlTop = $this->eRoot->appendChild($xmlTop);
            
            $actType = "unit_edit";            
        }
        else {
            $isEdit = false;
            $actType = "unit_add";
        }
        
        $xmlTop = $this->xmlDoc->createElement("act", 
            $actType);
        $xmlTop = $this->eRoot->appendChild($xmlTop);

        $xmlTop = $this->xmlDoc->createElement("owners");
        $xmlTop = $this->eRoot->appendChild($xmlTop);
        $q = "SELECT id,name FROM owners ORDER BY name";
        $res = $this->hDbConn->query($q);
        while ($qrow = $res->fetch(\PDO::FETCH_ASSOC)) {
            $xmlSubTop = $this->xmlDoc->createElement("owner",
                htmlentities($qrow['name']));
            $xmlSubTopAttr = $this->xmlDoc->createAttribute('id');
            $xmlSubTopAttr->value = $qrow['id']; 
            $xmlSubTop->appendChild($xmlSubTopAttr);
            if (($isEdit) &&
                    ($unit->owner_id === $qrow['id'])) {
                $xmlSubTopAttr = $this->xmlDoc->createAttribute('selected');
                $xmlSubTopAttr->value = 'true'; 
                $xmlSubTop->appendChild($xmlSubTopAttr);                
            }
            $xmlTop->appendChild($xmlSubTop);
        }
        $xmlTop = $this->xmlDoc->createElement("categories");
        $xmlTop = $this->eRoot->appendChild($xmlTop);
        $q = "SELECT id,name FROM categories ORDER BY name";
        $res = $this->hDbConn->query($q);
        while ($qrow = $res->fetch(\PDO::FETCH_ASSOC)) {
            $xmlSubTop = $this->xmlDoc->createElement("category",
                htmlentities($qrow['name']));
            $xmlSubTopAttr = $this->xmlDoc->createAttribute('id');
            $xmlSubTopAttr->value = $qrow['id']; 
            $xmlSubTop->appendChild($xmlSubTopAttr);
            if (($isEdit) &&
                    ($unit->cat_id === $qrow['id'])) {
                $xmlSubTopAttr = $this->xmlDoc->createAttribute('selected');
                $xmlSubTopAttr->value = 'true'; 
                $xmlSubTop->appendChild($xmlSubTopAttr);                
            }
            $xmlTop->appendChild($xmlSubTop);
        }
        $xmlTop = $this->xmlDoc->createElement("fdistricts");
        $xmlTop = $this->eRoot->appendChild($xmlTop);
        $q = "SELECT id,name FROM fdistricts ORDER BY name";
        $res = $this->hDbConn->query($q);
        while ($qrow = $res->fetch(\PDO::FETCH_ASSOC)) {
            $xmlSubTop = $this->xmlDoc->createElement("fdistrict",
                htmlentities($qrow['name']));
            $xmlSubTopAttr = $this->xmlDoc->createAttribute('id');
            $xmlSubTopAttr->value = $qrow['id']; 
            $xmlSubTop->appendChild($xmlSubTopAttr);
            if (($isEdit) AND
                    ($unit->getCityParam('fdist_id') === $qrow['id'])) {
                $xmlSubTopAttr = $this->xmlDoc->createAttribute('selected');
                $xmlSubTopAttr->value = 'true'; 
                $xmlSubTop->appendChild($xmlSubTopAttr);                
            }
            $xmlTop->appendChild($xmlSubTop);
        }
        $xmlTop = $this->xmlDoc->createElement("manufacturers");
        $xmlTop = $this->eRoot->appendChild($xmlTop);
        $q = "SELECT id,name FROM manufacturers ORDER BY name";
        $res = $this->hDbConn->query($q);
        while ($qrow = $res->fetch(\PDO::FETCH_ASSOC)) {
            $xmlSubTop = $this->xmlDoc->createElement("manufacturer",
                htmlentities($qrow['name']));
            $xmlSubTopAttr = $this->xmlDoc->createAttribute('id');
            $xmlSubTopAttr->value = $qrow['id']; 
            $xmlSubTop->appendChild($xmlSubTopAttr);
            if (($isEdit) AND
                    ($unit->manuf_id === $qrow['id'])) {
                $xmlSubTopAttr = $this->xmlDoc->createAttribute('selected');
                $xmlSubTopAttr->value = 'true'; 
                $xmlSubTop->appendChild($xmlSubTopAttr);                
            }
            $xmlTop->appendChild($xmlSubTop);
        }

        if ($isEdit) {
	        $xmlTop = $this->xmlDoc->createElement("cities");
	        $xmlTop = $this->eRoot->appendChild($xmlTop);
	        $q = sprintf("SELECT id,name FROM cities WHERE rd_id=%d",
	            $unit->getCityParam('reg_id'));
	        $res = $this->hDbConn->query($q);
	        while ($qrow = $res->fetch(\PDO::FETCH_ASSOC)) {
	            $xmlSubTop = $this->xmlDoc->createElement("city",
	                htmlentities($qrow['name']));
	            $xmlSubTopAttr = $this->xmlDoc->createAttribute('id');
	            $xmlSubTopAttr->value = $qrow['id']; 
	            $xmlSubTop->appendChild($xmlSubTopAttr);
	            if (($isEdit) AND
	                    ($unit->getCityParam('id') === $qrow['id'])) {
	                $xmlSubTopAttr = $this->xmlDoc->createAttribute('selected');
	                $xmlSubTopAttr->value = 'true'; 
	                $xmlSubTop->appendChild($xmlSubTopAttr);                
	            }
	            $xmlTop->appendChild($xmlSubTop);
	        }
        	
            $xmlTop = $this->xmlDoc->createElement("name", $unit->name);
            $xmlTop = $this->eRoot->appendChild($xmlTop);
            $xmlTop = $this->xmlDoc->createElement("description", 
            	$unit->description);
            $xmlTop = $this->eRoot->appendChild($xmlTop);
            $xmlTop = $this->xmlDoc->createElement("year", $unit->year);
            $xmlTop = $this->eRoot->appendChild($xmlTop);
            $xmlTop = $this->xmlDoc->createElement("price", $unit->price);
            $xmlTop = $this->eRoot->appendChild($xmlTop);

            if (isset($unit->mileage)) {
                $xmlTop = $this->xmlDoc->createElement("mileage", 
                    $unit->mileage);
                $xmlTop = $this->eRoot->appendChild($xmlTop);
            }

            if (isset($unit->op_time)) {
                $xmlTop = $this->xmlDoc->createElement("op_time", 
                    $unit->op_time);
                $xmlTop = $this->eRoot->appendChild($xmlTop);
            }
    
            $xmlTop = $this->xmlDoc->createElement("images");
            $xmlTop = $this->eRoot->appendChild($xmlTop);
            $aImg = $unit->img;
            foreach ($aImg as $img) {
                $xmlSubTop = $this->xmlDoc->createElement("img", $img);
                $xmlTop->appendChild($xmlSubTop);
            }
        }
    }
    
    function ownerForm() {
        if ($this->hWebPage->getGetValue('id')) {
	        $q = sprintf('
	    		SELECT id,name,description
	    		FROM `owners`
	    		WHERE id=%d',
	    		$this->hWebPage->getGetValue('id')
	    	);
	        $res = $this->hDbConn->query($q);
	        $owner = $res->fetch(\PDO::FETCH_ASSOC);
            
            $xmlTop = $this->xmlDoc->createElement('owner', 
                htmlentities($owner['description']));
            $xmlTop = $this->eRoot->appendChild($xmlTop);
            $xmlAttr = $this->xmlDoc->createAttribute('id');
            $xmlAttr->value = $owner['id']; 
            $xmlTop->appendChild($xmlAttr);
            $xmlAttr = $this->xmlDoc->createAttribute('name');
            $xmlAttr->value = $owner['name']; 
            $xmlTop->appendChild($xmlAttr);
        }
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
        // fill user login form
        if (isset($_SESSION["user"])) {
            $this->fillUser();
        }        

        $sUnitId = $this->xmlDoc->createElement("unit_id",
        	$this->hWebPage->getGetValue('id'));
        $this->eRoot->appendChild($sUnitId);
        
        // fill comments list
        $q = "SELECT 
	        		id,
	        		user_id,
	        		type,
	        		name,
	        		comment,
	        		approved
        		FROM comments
            	WHERE 
            		unit_id=%d AND
            		p_com_id IS NULL 
            	ORDER BY date ASC";
        $q = sprintf($q, $this->hWebPage->getGetValue('id'));
        $res = $this->hDbConn->query($q);        
        if ($res->rowCount() > 0) {
            $sComents = $this->xmlDoc->createElement("comments");
            $sComents = $this->eRoot->appendChild($sComents);
            
            while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
                $sComent = $this->xmlDoc->createElement("comment",
                    htmlentities($row['comment']));
                $sComent = $sComents->appendChild($sComent);
                $attr = $this->xmlDoc->createAttribute('name');
                $attr->value = $row['name'];
                $sComent->appendChild($attr);
                $attr = $this->xmlDoc->createAttribute('id');
                $attr->value = $row['id'];
                $sComent->appendChild($attr);
                $attr = $this->xmlDoc->createAttribute('user_id');
                $attr->value = $row['user_id'];
                $sComent->appendChild($attr);
                $attr = $this->xmlDoc->createAttribute('type');
                $attr->value = $row['type'];
                $sComent->appendChild($attr);
                $attr = $this->xmlDoc->createAttribute('approved');
                if (!isset($row['approved']) OR $row['approved'])
                    $attr->value = 'true';
                else
                    $attr->value = 'false';
                $sComent->appendChild($attr);

                $q = "SELECT 
                			id,
                			user_id,
                			type,name,
                			comment,
                			approved
                		FROM comments
                    	WHERE p_com_id=%d
                    	ORDER BY date ASC";
                $q = sprintf($q, $row['id']);
                $subRes = $this->hDbConn->query($q);
                while ($subRow = $subRes->fetch(\PDO::FETCH_ASSOC)) {
                    $sSubComent = $this->xmlDoc->createElement("comment",
                        htmlentities($subRow['comment']));
                    $sSubComent = $sComent->appendChild($sSubComent);
                    $attr = $this->xmlDoc->createAttribute('name');
                    $attr->value = $subRow['name'];
                    $sSubComent->appendChild($attr);
                    $attr = $this->xmlDoc->createAttribute('id');
                    $attr->value = $subRow['id'];
                    $sSubComent->appendChild($attr);
                    $attr = $this->xmlDoc->createAttribute('user_id');
                    $attr->value = $subRow['user_id'];
                    $sSubComent->appendChild($attr);
                    $attr = $this->xmlDoc->createAttribute('type');
                    $attr->value = $subRow['type'];
                    $sSubComent->appendChild($attr);
                    $attr = $this->xmlDoc->createAttribute('approved');
                    if (!isset($subRow['approved']) OR $subRow['approved'])
                        $attr->value = 'true';
                    else
                        $attr->value = 'false';
                    $sSubComent->appendChild($attr);
                }

            }
        }
    }

    function searchPaginator($page) {
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
        if ($this->hWebPage->getGetValue('vType') != 0) {
            $q .= sprintf(' AND cat_id=%d',
            	$this->hWebPage->getGetValue('vType'));
        }
        if ($this->hWebPage->getGetValue('vManuf')) {
            $q .= sprintf(' AND manufacturer_id=%d', 
            	$this->hWebPage->getGetValue(vManuf));
        }
        if ($this->hWebPage->getGetValue('vFedDistr')) {
            $q .= sprintf(' AND fd_id=%d', 
            	$this->hWebPage->getGetValue(vFedDistr));
        }

        $countRes = $this->hDbConn->query($q);
        $iTotal = $countRes->fetch(\PDO::FETCH_ASSOC)['total'];

        // current page number
        if ($this->hWebPage->getGetValue('offset')) {
            $iOffset = max($this->hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }
        
        $vType = $this->hWebPage->getGetValue('vType') ?
        	$this->hWebPage->getGetValue('vTyep') : 0;
        $vManuf = $this->hWebPage->getGetValue('vManuf') ?
        	$this->hWebPage->getGetValue('vManuf') : 0;
        $vFedDistr = $this->hWebPage->getGetValue('vFedDistr') ?
        	$this->hWebPage->getGetValue('vFedDistr') : 0;
        switch ($this->hWebPage->getGetValue('page')) {
            case "search":
            	$aVar = ['search', $vType, $vManuf, $vFedDistr, '%d'];
            	$sLinkPattern = htmlentities("/".implode("/", $aVar));
                break;
            case "admin":
            	$aVar = array(
            		'page'		=> $this->hWebPage->getGetValue('page'),
            		'act'		=> $this->hWebPage->getGetValue('act'),
            		'vType'		=> $vType,
            		'vManut'	=> $vManuf,
            		'vFedDistr'	=> $vFedDistr,
            		'offset'	=> '%d'
            	);
            	$sLinkPattern = htmlentities('?'.http_build_query($aVar));
                break;
        }
        
        $oPaginator = new CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );
        
		$this->eRoot->appendChild(
		    $this->xmlDoc->importNode(
		        $oPaginator->getXML(),
		        TRUE
		    )
		);
    }
    
    function unitArchList() {
        $q = 'SELECT u.id 
        		FROM units u
				JOIN cities ON u.city_id=cities.id
				JOIN regions ON cities.rd_id=regions.id
        		WHERE is_arch=TRUE';

        if ($this->hWebPage->getGetValue('vType')) {
            $q .= sprintf(" AND cat_id=%d",
            	$this->hWebPage->getGetValue('vType'));
        }
        if ($this->hWebPage->getGetValue('vManuf')) {
            $q .= sprintf(" AND manufacturer_id=%d", 
            	$this->hWebPage->getGetValue(vManuf));
        }
        if ($this->hWebPage->getGetValue('vFedDistr')) {
            $q .= sprintf(" AND fd_id=%d", 
            	$this->hWebPage->getGetValue(vFedDistr));
        }

        if ($this->hWebPage->getGetValue('offset')) {
            $iOffset = max($this->hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }        
        
        $q .= sprintf(" ORDER BY date DESC LIMIT %d,%d",
            ($iOffset-1)*PAGINATOR_SHOW_ON_PAGE,
            PAGINATOR_SHOW_ON_PAGE
        );

        $res = $this->hDbConn->query($q);
        while ($ur = $res->fetch(\PDO::FETCH_ASSOC)) {
        	$unit = $this->hWebPage->getUnit($ur['id']);
        	$unit = $unit->getUnitDOM();
			$unit = $this->xmlDoc->importNode($unit, true);
			$this->eRoot->appendChild($unit);
			unset($unit);
        }
	}
	
	function unitArchListPaginator() {
        $q = '
        	SELECT
			COUNT(*) AS total
			FROM units
			JOIN cities ON units.city_id=cities.id
			JOIN regions ON cities.rd_id=regions.id
			JOIN fdistricts ON regions.fd_id=fdistricts.id
			JOIN categories ON units.cat_id=categories.id
			JOIN manufacturers ON manufacturers.id=units.manufacturer_id
			WHERE is_arch=TRUE';

        $bNeedAND = false;
        if ($this->hWebPage->getGetValue('vType') != 0) {
            $q .= sprintf(' AND cat_id=%d',
            	$this->hWebPage->getGetValue('vType'));
        }
        if ($this->hWebPage->getGetValue('vManuf')) {
            $q .= sprintf(' AND manufacturer_id=%d', 
            	$this->hWebPage->getGetValue(vManuf));
        }
        if ($this->hWebPage->getGetValue('vFedDistr')) {
            $q .= sprintf(' AND fd_id=%d', 
            	$this->hWebPage->getGetValue(vFedDistr));
        }

        $countRes = $this->hDbConn->query($q);
        $iTotal = $countRes->fetch(\PDO::FETCH_ASSOC)['total'];

        // current page number
        if ($this->hWebPage->getGetValue('offset')) {
            $iOffset = max($this->hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }
        
        $vType = $this->hWebPage->getGetValue('vType') ?
        	$this->hWebPage->getGetValue('vTyep') : 0;
        $vManuf = $this->hWebPage->getGetValue('vManuf') ?
        	$this->hWebPage->getGetValue('vManuf') : 0;
        $vFedDistr = $this->hWebPage->getGetValue('vFedDistr') ?
        	$this->hWebPage->getGetValue('vFedDistr') : 0;

    	$aVar = array(
    		'page'		=> $this->hWebPage->getGetValue('page'),
    		'act'		=> $this->hWebPage->getGetValue('act'),
    		'vType'		=> $vType,
    		'vManut'	=> $vManuf,
    		'vFedDistr'	=> $vFedDistr,
    		'offset'	=> '%d'
    	);
    	$sLinkPattern = htmlentities('?'.http_build_query($aVar));
        
        $oPaginator = new CPaginator(
            $sLinkPattern,
            $iTotal,
            PAGINATOR_SHOW_ON_PAGE,
            $iOffset,
            PAGINATOR_PAGES_IN_NAV
        );
        
		$this->eRoot->appendChild(
		    $this->xmlDoc->importNode(
		        $oPaginator->getXML(),
		        TRUE
		    )
		);		
	}
    
    function searchPageMain() {
        $q = 'SELECT u.id 
        		FROM units u
				JOIN cities ON u.city_id=cities.id
				JOIN regions ON cities.rd_id=regions.id
        		WHERE is_arch=FALSE';

        if ($this->hWebPage->getGetValue('vType')) {
            $q .= sprintf(" AND cat_id=%d",
            	$this->hWebPage->getGetValue('vType'));
        }
        if ($this->hWebPage->getGetValue('vManuf')) {
            $q .= sprintf(" AND manufacturer_id=%d", 
            	$this->hWebPage->getGetValue(vManuf));
        }
        if ($this->hWebPage->getGetValue('vFedDistr')) {
            $q .= sprintf(" AND fd_id=%d", 
            	$this->hWebPage->getGetValue(vFedDistr));
        }

        if ($this->hWebPage->getGetValue('offset')) {
            $iOffset = max($this->hWebPage->getGetValue('offset'), 1);
        } else {
            $iOffset = 1;
        }        
        
        $q .= sprintf(" ORDER BY date DESC LIMIT %d,%d",
            ($iOffset-1)*PAGINATOR_SHOW_ON_PAGE,
            PAGINATOR_SHOW_ON_PAGE
        );

        $res = $this->hDbConn->query($q);
        while ($ur = $res->fetch(\PDO::FETCH_ASSOC)) {
        	$unit = $this->hWebPage->getUnit($ur['id']);
        	$unit = $unit->getUnitDOM();
			$unit = $this->xmlDoc->importNode($unit, true);
			$this->eRoot->appendChild($unit);
			unset($unit);
        }
    }

    function mainPageList() {
        $q = '
        	SELECT 
    			cat.id,
    			cat.name
    		FROM 
    			categories cat 
    		JOIN units u ON cat.id=u.cat_id 
    		WHERE u.is_arch = FALSE
    		GROUP BY cat.name 
    		HAVING COUNT(cat.id)>1';
        $cat_res = $this->hDbConn->query($q);
        while ($cr = $cat_res->fetch(\PDO::FETCH_ASSOC)) {
            $eCat = $this->xmlDoc->createElement('category');
            $eCatId = $this->xmlDoc->createAttribute('id');
            $eCatId->value = htmlentities($cr['id']);
            $eCat->appendChild($eCatId);              
            $eCatName = $this->xmlDoc->createAttribute('name');
            $eCatName->value = htmlentities($cr['name']);
            $eCat->appendChild($eCatName);              
            $eCat = $this->eRoot->appendChild($eCat);

			$q = 'SELECT id
					FROM units
					WHERE
						is_arch=FALSE AND
						cat_id=%d
					ORDER BY date DESC
					LIMIT 4';

            $q = sprintf($q, $cr['id']);
            $unit_res = $this->hDbConn->query($q);
            while ($ur = $unit_res->fetch(\PDO::FETCH_ASSOC)) {
            	$unit = $this->hWebPage->getUnit($ur['id']);
            	$unit = $unit->getUnitDOM();
				$unit = $this->xmlDoc->importNode($unit, true);
				$eCat->appendChild($unit);
				unset($unit);
            }
        }
    }
        
}

?>
