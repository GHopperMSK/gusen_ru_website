<?php
namespace gusenru;
// ЭТО ПИЗДЕЦ!
require_once 'vendor/dbrisinajumi/array2xml/array2xml.php';

use phpFastCache\CacheManager;

/**
 * CWebPage class
 * 
 * Provides next functionality:
 * - general the site logic
 * - URL and POST data processing 
 * - templates processing
 * - modules execution
 * - users access control
 * - data caching
 * 
 * Member $sPageContent contains HTML-code of the page.
 * The class looks for pattern %{MOD_NAME&MOD_PARAM}% in $sPageContent and
 * loads appropriate module MOD_NAME with params.
 * 
 * @param CDataBase $hDbConn
 * 
 * @return void
 */
class CWebPage
{
    private $_sTemplate;
    private $_sContent; // the whole page content
    private $_sHeader;
    
    private $_aGetValues = array();
    private $_aUnits = array();
    private $_aModules = array();
    
    private $_instanceCache;

    static private $_instance = NULL;
    
    private function __construct() {
    	if (DEBUG_MODE)
    		openlog('gusenru', LOG_NDELAY, LOG_USER);

        CWebPage::debug(__METHOD__);

		$this->_processGetValues();
		// TODO: ADD POST VARS
		// TODO: ADD SESSION VARS

		if (CACHE_ON) {
			CacheManager::setDefaultConfig([
				"path" => sys_get_temp_dir(),
			]);
			$this->_instanceCache = CacheManager::getInstance(CACHE_TYPE);
		}

		$this->_processPage($this->_aGetValues['page']);
        
    }

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() { }

	static public function getInstance()  {
		CWebPage::debug("CWebPage::getInstance()");
		
        if (self::$_instance == NULL) {
        	CWebPage::debug("CWebPage::getInstance() creating WebPage object");
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Processing of GET values
     */
    private function _processGetValues() {
    	foreach ($_GET as $key => $value) {
    		if (in_array($key, GET_INT_VALS)) {
				$value = filter_var(
                    $_GET[$key],
                    FILTER_SANITIZE_NUMBER_INT
                );
    		}
    		$this->_aGetValues[$key] = $value;
    	}
    }

    /**
     * choosing 'page' value from URL
     * http://yoursite.com?*page*=smth&param1=val1...
     * 
     * It defines a content which will be loaded.
     */
    private function _processPage($page) {
        switch ($page) {
            case 'comment_add':
                $this->commentAdd();
                break;
            case 'ajax':
                $this->_ajaxPage($this->_aGetValues['ajax_mode']);
                break;
            case 'oauth_vk':
                $this->oauth('vk');
                break;
            case 'oauth_gl':
                $this->oauth('gl');
                break;
            case 'oauth_fb':
                $this->oauth('fb');
                break;
            case 'logout':
                $this->userLogout();
                break;
            case 'unit':
                // if unit_id doesn't exists in the DB, send 404 page
                $stmt = CDataBase::getInstance()->prepare('
                	SELECT
						COUNT(*) as cnt 
					FROM units 
					WHERE id=:id'
				);
                $stmt->bindValue(
                    ':id',
                    $this->_aGetValues['id'],
                    \PDO::PARAM_INT
                );
                $stmt->execute();
                if ($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] == 1) {
                    $this->_setTemplate('tpl/unit.tpl');
                }
                else {
                    header('Location: /404');
                }                    
                break;
            case 'search':
                $this->_setTemplate('tpl/search.tpl');
                break;
            case 'about':
                $this->_setTemplate('tpl/about.tpl');
                break;
            case 'admin':
                $this->_adminPage($this->_aGetValues['act']);
                break;
            case 'sitemap':
                $this->_sitemap();
                break;
            case 'copyright':
                $this->_setTemplate('tpl/copyright.tpl');
                break;
            case '404':
                $this->_setTemplate('tpl/404.tpl');
                break;
            case 'error':
            	$this->_setTemplate('tpl/error.tpl');
            	break;
            case 'main':
            default:
                $this->_setTemplate('tpl/main.tpl');
        }        
    }
    
    /**
     * Sets current page template and defines all modules within it
     * 
     * @param string $tpl template name
     * 
     * @return void
     */
    private function _setTemplate($tpl) {
        CWebPage::debug("CWebPage::_setTemplate({$tpl})");
        
        if (file_exists($tpl)) {
            $this->_sTemplate = $tpl;
            
            $this->_sContent = file_get_contents($this->_sTemplate);
            if (!$this->_sContent)
            	throw new \Exception("CWebPage::renderTemplate({$tpl}): ".
            		"Can't load template or it's empty");
            
            preg_match_all('/%{(.+)}%/', $this->_sContent, $aModules);

            foreach ($aModules[1] as $key => $value) {
                list($modName, $viewFile, $duration, $param1, $param2) = 
                	explode('&', $value);

                if (empty($modName) || empty($viewFile) || !isset($duration)) {
					throw new \Exception("Error module parameters! {$aModules[0][$key]}");
                }
                
                $this->_aModules[$aModules[0][$key]] = array(
                	'name' => $modName,
                	'view' => $viewFile == 'null' ? NULL : $viewFile,
                	'duration' => $duration,
                	'param1' => empty($param1) ? NULL : $param1,
                	'param2' => empty($param2) ? NULL : $param2//,
            	);
            }
        }
        else
        	throw new \Exception("TPL {$tpl} doesn't exists!");
    }
    
    /**
     * Runs all modules from the page and applies the corresponding
     * views (if they are exist)
     * 
     * @return void 
     */
    private function _render() {
        CWebPage::debug('CWebPage::renderTemplate()');

		$xml = new \DOMDocument;
        $xsl = new \DOMDocument();
        $hProc = new \XSLTProcessor();
		$array2xml = new \Array2xml();
		$array2xml->setFilterNumbersInTags(TRUE);
        
    	foreach ($this->_aModules as $key => $value) {
			$sModContent = '';

			$sCache = NULL;
			if (CACHE_ON && ($value['duration'] > 0)) {
				$sUrl = http_build_query($this->_aGetValues);

				$sCache = $this->_instanceCache->getItem(
					md5(
				    	$value['name'].
				    	$value['view'].
				    	$value['param1'].
				    	$value['param2'].
						$sUrl // add URL-specific key
					)
				);

				if ($sCache->isHit()) {
					CWebPage::debug("Get from cache {$key}");

					$sModContent = $sCache->get();						
				}

			}
			
			if (empty($sModContent)) {
				$hMod = new CModule(
			    	$value['name'],
			    	$value['param1'],
			    	$value['param2']
				);
				$aData = $hMod->execute();
		        unset($hMod);

	    		if ($value['view']) {
		    		$sXml = $array2xml->convert($aData);
	
					$xml->loadXML($sXml);
	                $xsl->load("xsl/{$value['name']}/{$value['view']}");	            
		            $hProc->importStylesheet($xsl);
		            $sModContent = $hProc->transformToXML($xml);
	    		} else {
	    			$sModContent = $aData[0];
	    		}
	    		
// if ($value['name'] == 'unit_comments') {
// 	d($sModContent);
// 	exit;
// }
			    
			    if (CACHE_ON && ($value['duration'] > 0)) {
					CWebPage::debug("Write to cache {$key} on {$duration} minutes");
				
					$sCache
						->set($sModContent)
						->expiresAfter($value['duration'] * 60);
				    $this->_instanceCache->save($sCache);
			    }
			    elseif (CACHE_ON && ($value['duration'] == 0)) {
			    	CWebPage::debug("{$key} doesn't use a cache");
			    }
			}
			unset($sCache);
			
		    $this->_sContent = str_replace(
		    	$key,
		    	$sModContent,
		    	$this->_sContent
		    );
		    unset($sModContent);
    	}
    }
    
    function resetCache() {
    	CWebPage::debug('CWebPage::resetCache()');
		
		if (CACHE_ON)
			$this->_instanceCache->clear();
    }
    
    function getContent() {
		// $this->_executeModules();
    	$this->_render();
    	
    	if (!empty($this->_sHeader)) {
    		header($this->_sHeader);
    	}
        return $this->_sContent;
    }
    
    public function __toString() {
        return $this->getContent();
    }
    
    function getGetValue($name) {
    	if (array_key_exists($name, $this->_aGetValues))
    		return $this->_aGetValues[$name];
    	else
    		return FALSE;
    }
    
    function getUnit($id) {
    	if (!array_key_exists($id, $this->_aUnits))
			$this->_aUnits[$id] = new CUnit($id);

    	return $this->_aUnits[$id];
    }
    
    static function debug($message, $priority = LOG_INFO) {
    	if (DEBUG_MODE) {
			syslog($priority, $message);
    	}
    }
    
    // WBMP->resourse convertor
    private function ImageCreateFromBMP($filename) {
        if (! $f1 = fopen($filename,'rb')) return FALSE; 
        $FILE = unpack('vfile_type/Vfile_size/Vreserved/Vbitmap_offset', fread($f1,14)); 
        if ($FILE['file_type'] != 19778) return FALSE; 
        $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'. 
        '/Vcompression/Vsize_bitmap/Vhoriz_resolution'. 
        '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40)); 
        $BMP['colors'] = pow(2,$BMP['bits_per_pixel']); 
        if ($BMP['size_bitmap'] == 0)
            $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset']; 
        $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8; 
        $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']); 
        $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4); 
        $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4); 
        $BMP['decal'] = 4-(4*$BMP['decal']); 
        if ($BMP['decal'] == 4) $BMP['decal'] = 0; 
            $PALETTE = array(); 
        if ($BMP['colors'] < 16777216) { 
            $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4)); 
        } 
        $IMG = fread($f1,$BMP['size_bitmap']); 
        $VIDE = chr(0); 
        $res = imagecreatetruecolor($BMP['width'],$BMP['height']); 
        $P = 0; 
        $Y = $BMP['height']-1; 
        while ($Y >= 0) { 
            $X=0; 
            while ($X < $BMP['width']) { 
                if ($BMP['bits_per_pixel'] == 24) 
                    $COLOR = unpack('V',substr($IMG,$P,3).$VIDE); 
                elseif ($BMP['bits_per_pixel'] == 16) {   
                    $COLOR = unpack('n',substr($IMG,$P,2)); 
                    $COLOR[1] = $PALETTE[$COLOR[1]+1]; 
                } 
                elseif ($BMP['bits_per_pixel'] == 8) {   
                    $COLOR = unpack('n',$VIDE.substr($IMG,$P,1)); 
                    $COLOR[1] = $PALETTE[$COLOR[1]+1]; 
                } 
                elseif ($BMP['bits_per_pixel'] == 4) { 
                    $COLOR = unpack('n',$VIDE.substr($IMG,floor($P),1)); 
                    if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F); 
                    $COLOR[1] = $PALETTE[$COLOR[1]+1]; 
                } 
                elseif ($BMP['bits_per_pixel'] == 1) { 
                    $COLOR = unpack('n',$VIDE.substr($IMG,floor($P),1)); 
                    if (($P*8)%8 == 0)
                        $COLOR[1] =  $COLOR[1] >> 7; 
                    elseif (($P*8)%8 == 1)
                        $COLOR[1] = ($COLOR[1] & 0x40) >> 6; 
                    elseif (($P*8)%8 == 2)
                        $COLOR[1] = ($COLOR[1] & 0x20)>>5; 
                    elseif (($P*8)%8 == 3)
                        $COLOR[1] = ($COLOR[1] & 0x10)>>4; 
                    elseif (($P*8)%8 == 4)
                        $COLOR[1] = ($COLOR[1] & 0x8)>>3; 
                    elseif (($P*8)%8 == 5)
                        $COLOR[1] = ($COLOR[1] & 0x4)>>2; 
                    elseif (($P*8)%8 == 6)
                        $COLOR[1] = ($COLOR[1] & 0x2)>>1; 
                    elseif (($P*8)%8 == 7)
                        $COLOR[1] = ($COLOR[1] & 0x1); 
                    $COLOR[1] = $PALETTE[$COLOR[1]+1]; 
                } 
                else 
                    return FALSE; 
                imagesetpixel($res,$X,$Y,$COLOR[1]); 
                $X++; 
                $P += $BMP['bytes_per_pixel']; 
            } 
            $Y--; 
            $P+=$BMP['decal']; 
        } 
        fclose($f1); 
        return $res; 
    } 

    /**
     * 
     * Resize JPG image
     * 
     * @param string $file original image file name
     * @param int $w new width value
     * @param int $h new height value
     * $param bool $crop the flag shows if we should  crop the image
     * 
     * @return resource resized image resource
     */
    private function _resizeImage($file, $w, $h, $crop=FALSE) {
        CWebPage::debug("CWebPage::_resizeImage({$file}, {$w}, {$h}, {$crop})");

        // get image type
        $src = null;
        switch (exif_imagetype($file)) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($file);
                break;
            case IMAGETYPE_BMP:
                //$src = imagecreatefromwbmp($file);
                $src = $this->ImageCreateFromBMP($file);
                break;
        }
        if ($src) {
            $exif = @exif_read_data($file);
            if(!empty($exif['Orientation'])) {
                switch($exif['Orientation']) {
                    case 8:
                        $src = imagerotate($src,90,0);
                        break;
                    case 3:
                        $src = imagerotate($src,180,0);
                        break;
                    case 6:
                        $src = imagerotate($src,-90,0);
                        break;
                }
            }    
        
            $sWidth = imagesx($src);
            $sHeight = imagesy($src); 

            /**
             * if one of the image dimension bigger than we need
             * then resize it else leave it as is
             */
            if (($sWidth>$w) OR ($sHeight>$h)) {
                if ($crop) {
                    $minSize = min($sWidth, $sHeight);
                    $height = $minSize;
                    $width = $minSize;
                    $newwidth = $w;
                    $newheight = $h;
                } else {
                    $r = $sWidth / $sHeight;
                    if ($w/$h > $r) {
                        $newwidth = $h*$r;
                        $newheight = $h;
                    } else {
                        $newheight = $w/$r;
                        $newwidth = $w;
                    }
                    $height = $sHeight;
                    $width = $sWidth;
                }
            }
            else {
                $newwidth = $sWidth;
                $newheight = $sHeight;                
                $height = $sHeight;
                $width = $sWidth;
            }
        
            $dst = imagecreatetruecolor($newwidth, $newheight);
            imagecopyresampled($dst, $src,
                0, 0, 
                ($sWidth-$width)/2, ($sHeight-$height)/2, 
                $newwidth, $newheight, 
                $width, $height);
    
            // Add watermark
            if (false) {            
                // Load the stamp and the photo to apply the watermark to
                $stamp = imagecreatefrompng('img/gusen.png');
                
                // Set the margins for the stamp and get the height/width of the stamp image
                $marge_right = 10;
                $marge_bottom = 10;
                $sx = imagesx($stamp);
                $sy = imagesy($stamp);
                
                // Copy the stamp image onto our photo using the margin offsets and the photo 
                // width to calculate positioning of the stamp. 
                imagecopy($dst, $stamp, imagesx($dst) - $sx - $marge_right, imagesy($dst) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
            }
    
            return $dst;
        }
        else 
            return FALSE;
    }
    
    /**
     * Checks if user is authorized 
     * 
     * @return bool
     */
    private function isAuth() {
        CWebPage::debug('CWebPage::isAuth()');

        if(isset($_SESSION['username'])) {
            return TRUE;
        }
        else    
            return FALSE;
    }

    //======================================================================
    
    /**
     * oAuth login via
     *  FaceBook
     *  VKontakte
     *  Google
     * accounts
     * 
     * @param string $type can be: 'vk','fb','gl'
     * 
     * @return void
     */
    private function oauth($type) {
        CWebPage::debug("CWebPage::oauth({$type})");
        
        list($realHost,)=explode(':',$_SERVER['HTTP_HOST']);

        $cur_link = sprintf('https://%s/?page=%s',
            $realHost,
            $this->_aGetValues['page']
        );
        
        $getUser = FALSE;    
        
        switch ($type) {
            case 'vk':
				$vk = new \VK\VK(VK_CLIENT_ID, VK_SECRET);
				$vk->setApiVersion(VK_VERSION);
            	try {
					$access_token = $vk->getAccessToken(
						$_REQUEST['code'],
						sprintf("https://%s/?page=oauth_vk", $realHost)
					);
            	}
            	catch (\VK\VKException $exception) {
            		throw new \Exception($exception);
            	}
					
				$user = $vk->api('users.get', array(
					'uids'   => $access_token->user_id,
					'fields' => 'first_name,last_name,sex,photo_50')
				);
				if (!empty($user['response'][0]['id'])) {
					$userInfo['uid'] = $access_token['user_id'];
					$userInfo['name'] = $user['response'][0]['first_name'].
						' '.$user['response'][0]['last_name'];
					$userInfo['photo'] = $user['response'][0]['photo_50'];
					$userInfo['email'] = $access_token['email'];
					$userInfo['gender'] = $user['response'][0]['sex'];
					$userInfo['type'] = 'vk';
					$getUser = TRUE;
				}
                break;
            case 'fb':
				$fb = new \Facebook\Facebook([
					'app_id' => FB_CLIENT_ID,
					'app_secret' => FB_SECRET,
					'default_graph_version' => FB_VERSION,
				]);
							
				$helper = $fb->getRedirectLoginHelper();
				
				try {
					$accessToken = $helper->getAccessToken();
				} catch(\Facebook\Exceptions\FacebookResponseException $e) {
					// When Graph returns an error
					throw new \Exception($e);
				} catch(\Facebook\Exceptions\FacebookSDKException $e) {
					// When validation fails or other local issues
					throw new \Exception($e);
				}
				
				if (!isset($accessToken)) {
					if ($helper->getError()) {
						throw new \Exception(
							$helper->getError().PHP_EOL.
							$helper->getErrorReason().PHP_EOL.
							$helper->getErrorDescription()
						);
					} else {
						throw new \Exception("Can't get access Token!");
					}
				}
				
				$fb->setDefaultAccessToken($accessToken->getValue());
				$response = $fb->get('/me?locale=en_US&fields=id,name,gender,picture,email');
				$userNode = $response->getGraphUser();
				
				if (!empty($userNode['id'])) {
					$userInfo['uid'] = $userNode->getId();
					$userInfo['name'] = $userNode->getName();
					$userInfo['photo'] = $userNode->getPicture()->getUrl();
					$userInfo['email'] = $userNode->getEmail();
					$userInfo['gender'] = $userNode->getGender();
					$userInfo['type'] = 'fb';
					$getUser = TRUE;
				}
                break;
            case 'gl':
				$client = new \Google_Client();
				$client->setAccessType('online');
				$client->setClientId(GL_CLIENT_ID);
				$client->setClientSecret(GL_SECRET);
				$client->setRedirectUri($cur_link);
				
				$client->setScopes(array(
					'https://www.googleapis.com/auth/userinfo.email',
					'https://www.googleapis.com/auth/userinfo.profile')
				);
				
				try {
					$client->authenticate($this->_aGetValues['code']);
					$client->setAccessToken($client->getAccessToken());
					
					$objOAuthService = new \Google_Service_Oauth2($client);
					$userData = $objOAuthService->userinfo->get();
				}
				catch (\InvalidArgumentException $exception) {
					throw new \Exception($exception);
				}
				catch (\Google_Exception $exception) {
						throw new \Exception($exception);
				}
				if ($userData) {
				    $userInfo['uid'] = $userData->getId();
				    $userInfo['name'] = $userData->getName();
				    $userInfo['photo'] = $userData->getPicture().'?sz=50';
				    $userInfo['email'] = $userData->getEmail();
				    $userInfo['gender'] = $userData->getGender();
				    $userInfo['type'] = 'gl';
				    $getUser = TRUE;
				}  
                break;
        }
        if ($getUser) {
            $_SESSION['user']['id'] = $userInfo['uid'];
            $_SESSION['user']['type'] = $userInfo['type'];
            $_SESSION['user']['name'] = $userInfo['name'];
            $_SESSION['user']['photo'] = $userInfo['photo'];
		    $_SESSION['user']['email'] = $userInfo['email'];
		    $_SESSION['user']['gender'] = $userInfo['gender'];

            $loc = $_SESSION['user_referer'];
            unset($_SESSION['user_referer']);
            
            header("Location: $loc");
        }
    }
    
    private function userLogout() {
        CWebPage::debug('CWebPage::userLogout()');

		$this->resetCache();
        
        unset($_SESSION['user']);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
    private function commentAdd() {
        CWebPage::debug('CWebPage::commentAdd()');
        if (isset($_POST['user_id'])) {
            if (!filter_var($_POST['unit_id'], FILTER_VALIDATE_INT)) {
                echo 'Wrong parameters were passed!';
            }
            else {        
                $stmt = CDataBase::getInstance()->prepare("
                		INSERT 
                		INTO comments (
                			unit_id,
                			user_id,
                			p_com_id,
                			type,
                			name,
                			comment
                		) 
                		VALUES (:id, :uid, :p_com_id, :type, :name, :comment)");
                $stmt->bindValue(':id', $_POST['unit_id'], \PDO::PARAM_INT);
                $stmt->bindValue(':uid', $_POST['user_id'], \PDO::PARAM_INT);
                $stmt->bindValue(
                	':p_com_id',
                	isset($_POST['p_com_id']) ? $_POST['p_com_id'] : NULL, 
                	\PDO::PARAM_INT
                );
                $stmt->bindValue(':type', $_POST['type'], \PDO::PARAM_STR);
                $stmt->bindValue(
            		':name', 
            		$_SESSION['user']['name'], 
            		\PDO::PARAM_STR
            	);
                $stmt->bindValue(':comment', $_POST['comment'], \PDO::PARAM_STR);
                $stmt->execute();
            }
        }
        
		$this->resetCache();

        header('Location: ' . $_SERVER['HTTP_REFERER'].'#comment_form');
    }
    
    // generator of sitexml.xml
    private function _sitemap() {
        CWebPage::debug('CWebPage::sitemap()');
        
        $hDbConn = CDataBase::getInstance();
        
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $urlset = $xml->createElement('urlset');
        $urlset = $xml->appendChild($urlset);
        $attr = $xml->createAttribute('xmlns');
        $attr->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $urlset->appendChild($attr);

        $q = 'SELECT date FROM units ORDER BY date LIMIT 1';
        $res = $hDbConn->query($q);
        $row = $res->fetch(\PDO::FETCH_ASSOC);

        $url = $xml->createElement('url');
        $l = $xml->createElement('loc',
                sprintf('https://%s', $_SERVER['HTTP_HOST'])
        );
        $url->appendChild($l);
        $l = $xml->createElement('lastmod', 
                date('Y-m-d', strtotime($row['date'])
            )
        );
        
        $url->appendChild($l);
        $l = $xml->createElement('changefreq', 'weekly');
        $url->appendChild($l);
        $l = $xml->createElement('priority', '1');
        $url->appendChild($l);        
        $urlset->appendChild($url);

        $url = $xml->createElement('url');
        $l = $xml->createElement('loc',
                sprintf('https://%s/about', $_SERVER['HTTP_HOST'])
        );
        $url->appendChild($l);
        $l = $xml->createElement('lastmod', 
                date('Y-m-d', strtotime($row['date'])
            )
        );
        
        $url->appendChild($l);
        $l = $xml->createElement('changefreq', 'monthly');
        $url->appendChild($l);
        $l = $xml->createElement('priority', '0.3');
        $url->appendChild($l);        
        $urlset->appendChild($url);

        $q = 'SELECT id,date FROM units WHERE is_arch=FALSE';
        $res = CDataBase::getInstance()->query($q);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            $url = $xml->createElement('url');
            $l = $xml->createElement('loc',
                    htmlentities(
                        sprintf('https://%s/unit/%d',
                            $_SERVER['HTTP_HOST'], $row['id']
                    )
                )
            );
            $url->appendChild($l);
            $l = $xml->createElement('lastmod', 
                    date('Y-m-d', strtotime($row['date'])
                )
            );
            $url->appendChild($l);
            $l = $xml->createElement('changefreq', 'monthly');
            $url->appendChild($l);
            $l = $xml->createElement('priority', '0.5');
            $url->appendChild($l);
            
            $urlset->appendChild($url);
        }
        
        $this->_sHeader = 'Content-type: application/xml';
        $this->_sContent = $xml->saveXML();
    }
    
    //-----------------------------------------------------
    // Admin page functionality
    //-----------------------------------------------------    
    private function _adminPage($act) {
        switch ($act) {
            case 'check_login':
                if(isset($_POST['username'], $_POST['password'])) {
                    ob_start();
                    if (($_POST['username'] === ADMIN_NAME) AND 
                        (md5($_POST['password']) === ADMIN_PASS))
                    {
                        $_SESSION['username']=$_POST['username'];
                        header('location: ?page=admin&act=main');
                    }
                    else {
                        header('Location: ?page=admin&act=login_form'.
                            '&msg=Wrong_user_data'
                        );
                    }
                    ob_end_flush();
                }
                else {
                    header('Location: ?page=admin&act=main');
                }
                break;
            case 'logout':
                session_destroy();
                $_SESSION = array();
                header('Location: ?page=admin&act=login_form&msg=just_logout');
                break;
            case 'unit_arch_list':
                if ($this->isAuth())
                    $this->_setTemplate('tpl/admin_unit_arch_list.tpl');
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'admin_unit_form':
                if ($this->isAuth())
                    $this->_setTemplate('tpl/admin_unit_form.tpl');
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unit_edit':
                if ($this->isAuth()) {
                    $this->fillUnit('edit'); //$this->editUnit();
            		$this->resetCache();
    		    	header('Location: /?page=admin&act=main');
                }
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unit_add':
                if ($this->isAuth()) {
                    $this->fillUnit('add'); //$this->addUnit();
            		$this->resetCache();
            		header('Location: /?page=admin&act=main');
                }
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');                
                break;
            case 'unit_del':
                if ($this->isAuth()) {
                	CUnit::deleteUnit($this->_aGetValues['id']);
                	$this->resetCache();
					header('Location: ' . $_SERVER['HTTP_REFERER']);
                }
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unit_arch':
                if ($this->isAuth()) {
					CUnit::archUnit($this->_aGetValues['id']);
					header('Location: ' . $_SERVER['HTTP_REFERER']);
                }
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unit_restore':
                if ($this->isAuth()) {
					CUnit::restoreUnit($this->_aGetValues['id']);
					header('Location: ' . $_SERVER['HTTP_REFERER']);
                }
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
            	break;
            case 'owner_delete':
                if ($this->isAuth()) {
					$this->ownerDelete();
                }
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
            	break;
			case 'owner_edit':
				$this->ownerEdit();
				break;
			case 'owner_add':
				$this->ownerAdd();
				break;
            case 'owner_form':
            		$this->_setTemplate('tpl/admin_owner_form.tpl');
            	break;
            case 'main':
                if ($this->isAuth())
                    $this->_setTemplate('tpl/admin.tpl');
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unapproved_comments':
                if ($this->isAuth())
                    $this->_setTemplate('tpl/admin_unapproved_comments_list.tpl');
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'owners_list':
                if ($this->isAuth())
                    $this->_setTemplate('tpl/admin_owners_list.tpl');
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'login_form':
            default:
                if ($this->isAuth()) {
                    header('Location: ?page=admin&act=main');
                }
                else {
                    $this->_setTemplate('tpl/admin_login.tpl');
                }                    
                break;
        }        
    }
    
    private function fillUnit($mode) {
        CWebPage::debug("CWebPage::fillUnit({$mode})");

    	$unit = new CUnit($this->hDbConn);
    	$unit->cat_id = $_POST['category'];
    	$unit->manuf_id = $_POST['manufacturer'];
    	$unit->name = $_POST['name'];
    	$unit->owner_id = $_POST['owner'];
    	$unit->description = $_POST['description'];
    	$unit->price = $_POST['price'];
    	$unit->year = $_POST['year'];
    	$unit->mileage = $_POST['mileage'];
    	$unit->op_time = $_POST['op_time'];
        $unit->img= $_POST['images'];
        $unit->setCityParam('id', $_POST['city']);

		switch ($mode) {
			case "add":
		        $unit->addUnit();
				break;
			case "edit":
				$unit->id = $_POST['id'];
		        $unit->editUnit($_POST['available_images']);
				break;
		}
		$this->aUnits[$unit->id] = $unit;
    }
    
    private function ownerAdd() {
        $stmt = CDataBase::getInstance()->prepare('
    		INSERT INTO owners (
    			name,
    			description)
    		VALUES (
    			:name,
    			:descr)'
		);
        $stmt->bindValue(':name', $_POST['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':descr', $_POST['description'], \PDO::PARAM_STR);
        $stmt->execute();

    	header('Location: /?page=admin&act=owners_list');
    }
    
    private function ownerEdit() {
        $stmt = CDataBase::getInstance()->prepare('
    		UPDATE owners
    		SET
    			name=:name,
    			description=:descr
    		WHERE id=:owner_id'
		);
        $stmt->bindValue(':owner_id' ,$_POST['id'], \PDO::PARAM_INT);
        $stmt->bindValue(':name', $_POST['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':descr', $_POST['description'], \PDO::PARAM_STR);
        $stmt->execute();
    	
    	header('Location: /?page=admin&act=owners_list');
    }
    
    private function ownerDelete() {
    	$hDbConn = CDataBase::getInstance();
    	
        $q = sprintf('
    		SELECT COUNT(*) AS cnt
    		FROM `units`
    		WHERE owner_id=%d',
    		$this->getGetValue('id')
    	);
        $res = $hDbConn->query($q);
        if ($res->fetch(\PDO::FETCH_ASSOC)['cnt'] > 0) {
        	$this->_sContent = 'The owner is used! Remove it from all units before deleting.';
        } else {
        	$q = sprintf('DELETE FROM `owners` WHERE id=%d', 
        		$this->getGetValue('id'));
        	$hDbConn->query($q);
        	header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
    
    //-----------------------------------------------------
    // Ajax page functionality
    //-----------------------------------------------------    
    private function _ajaxPage($mode) {
        CWebPage::debug("CWebPage::ajaxPage({$mode})");

        switch ($mode) {
            case 'city':
                $json = array();
                $q = sprintf('
                	SELECT
    					id,
    					name 
    				FROM `cities` 
    				WHERE `rd_id` IN (
    					SELECT id 
    					FROM `regions` 
    					WHERE fd_id=%d
    				) 
    				ORDER BY name', 
                    filter_var(
                    	$this->_aGetValues['fdid'],
                    	FILTER_SANITIZE_NUMBER_INT
                    )
                );
                if ($res = CDataBase::getInstance()->query($q)) {
                    while ($r = $res->fetch(\PDO::FETCH_ASSOC)) {
                        $json[] = array(
                            'id' => $r['id'],
                            'name' => $r['name']
                        );
                    }
                }
                $this->_header = 'Content-Type: application/json; charset=UTF-8';
                $this->_sContent = json_encode($json);
                break;
            case 'image_load':
                if ($_FILES['afile']['error'] === UPLOAD_ERR_OK ) {
                    $fName = uniqid() . '.jpeg';
                
                    $img = $this->_resizeImage($_FILES['afile']['tmp_name'], 1280, 1024);
                    $tmb = $this->_resizeImage($_FILES['afile']['tmp_name'], 190, 190, TRUE);
                
                    imagejpeg($img, 'tmp_images/'.$fName);
                    imagejpeg($tmb, 'tmp_images/tmb/'.$fName);

                    $fileName = $_FILES['afile']['name'];
                    $fileType = $_FILES['afile']['type'];
                
                    $fileContent = file_get_contents('tmp_images/tmb/'.$fName);
                    $dataUrl = 'data:' . $fileType . ';base64,' . base64_encode($fileContent);
                    $json = json_encode(array(
                    	'name' => $fName,
                    	'type' => $fileType,
                    	'dataUrl' => $dataUrl,
                    ));
                    $this->_sContent = $json;
                }
                break;
            case 'vk_upload':
				if (isset($_POST['url']) && isset($_POST['unit_id'])) {
				//if (true) {
				//    $_POST['unit_id'] = 37;
				//    $_POST['url'] = "https://pu.vk.com/c604829/upload.php?act=do_add&mid=47192372&aid=-14&gid=137789409&hash=0cf4ca2d4e19002f61d503083505b44b&rhash=9044348098bb9c9d2309bca8b493138b&swfupload=1&api=1&wallphoto=1";
				    
				    $hDbConn = CDataBase::getInstance();
				    
                    $unit_id = filter_var(
                        $_POST['unit_id'], 
	                    FILTER_SANITIZE_NUMBER_INT
	                );

					$photos = array();

	                $q = sprintf('SELECT img
	                				FROM `images` 
	                				WHERE `unit_id` = %d
	                				ORDER BY `order`
	                				LIMIT 6', 
	                				$unit_id
	                );
	                
	                if ($res = $hDbConn->query($q)) {
	                	$i = 0;
	                    while ($r = $res->fetch(\PDO::FETCH_ASSOC)) {
	                    	$i++;
							$photos["file{$i}"] = new \CURLFile(
								realpath(
									$_SERVER["DOCUMENT_ROOT"].
									'/images/'.
									$r['img']
								)
							);
	                    }
	                }
	                
				    $ch = curl_init();
				    curl_setopt($ch, CURLOPT_URL, $_POST["url"]);
				    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				    curl_setopt($ch, CURLOPT_POST, TRUE);
				    curl_setopt($ch, CURLOPT_POSTFIELDS, $photos);
				    $result = curl_exec($ch);
				    curl_close($ch);

                    $unit = new CUnit($unit_id);

				    $aResult = json_decode($result, TRUE);
				    $aResult['unit'] = $unit->jsonData();
				    $result = json_encode($aResult);
				
					$this->_sContent = $result;
				}            	
            	break;
        }
    }

}

?>