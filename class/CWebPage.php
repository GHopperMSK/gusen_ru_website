<?php
namespace gusenru;

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
    protected $sPageContent = ''; // whole page content
    private $hDbConn;
    
    function __construct($hDbConn) {
    	
        if ($hDbConn instanceof CDataBase) {
            $this->hDbConn = $hDbConn;
        }
        else {
            echo 'Wrong CDataBase connection was passed!';
            exit;
        }

        // process integer get-values within URL
        $aGetInt = ['id', 'vType', 'vManuf', 'vFedDistr'];
        foreach ($aGetInt as $var) {
            if (isset($_GET[$var])) {
                $_GET[$var] = filter_var(
                    $_GET[$var],
                    FILTER_SANITIZE_NUMBER_INT
                );
            }
        }

        $this->pageProcess($_GET['page']);
    }

    /**
     * choosing 'page' value from URL
     * http://yoursite.com?*page*=smth&param1=val1...
     * 
     * It defines a content which will be loaded.
     */
    private function pageProcess($page) {
        switch ($page) {
            case 'comment_add':
                $this->commentAdd();
                break;
            case 'ajax':
                $this->ajaxPage($_GET['ajax_mode']);
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
                $stmt = $this->hDbConn->prepare('
                	SELECT 
						COUNT(*) as cnt 
					FROM units 
					WHERE id=:id'
				);
                $stmt->bindValue(
                    ':id',
                    $_GET['id'],
                    \PDO::PARAM_INT
                );
                $stmt->execute();
                if ($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] == 1) {
                    $this->setTemplate('tpl/unit.tpl');
                }
                else {
                    header('Location: ?page=404');
                    exit;
                }                    
                break;
            case 'search':
                $this->setTemplate('tpl/search.tpl');
                break;
            case 'about':
                $this->setTemplate('tpl/about.tpl');
                break;
            case 'admin':
                $this->adminPage($_GET['act']);
                break;
            case 'sitemap':
                $this->sitemap();
                break;
            case 'copyright':
                $this->setTemplate('tpl/copyright.tpl');
                break;
            case '404':
                $this->setTemplate('tpl/404.tpl');
                break;
            case 'main':
            default:
                $this->setTemplate('tpl/main.tpl');
        }        
    }
    
    /**
     * Applies filter_var for each member of given array
     * with specified validate filter
     * 
     * @param array $aVar
     * @param int $secKey
     * 
     * @return bool
     */
    function varValid($aVar, $secKey) {
        foreach ($aVar as $var) {
            if (filter_var($var, $secKey) === FALSE) {
                return false;
            }
        }
        return true;
    }    
    
    // WBMP->resourse convertor
    function ImageCreateFromBMP($filename) { 
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
    function resizeImage($file, $w, $h, $crop=FALSE) {
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
            return false;
    }
    
    /**
     * Checks if user is authorized 
     * 
     * @return bool
     */
    function isAuth() {
        if(isset($_SESSION['username'])) {
            return TRUE;
        }
        else    
            return FALSE;
    }

    /**
     * Sets current page template
     * 
     * @param string $tpl template name
     * 
     * @return void
     */
    function setTemplate($tpl) {
        if (file_exists($tpl)) {
            $this->tpl = $tpl;
        }
        else {
            $this->sPageContent = "TPL $tpl doesn't exists!";
        }        
    }

    /**
     * Processing template and executing all modules from it.
     * Eventually sets $sPageContent content.
     * 
     * @return void 
     */
    function renderTemplate() {
        if (isset($this->tpl)) {
            $sPage = file_get_contents($this->tpl);
            
            preg_match_all('/%{(.+)}%/', $sPage, $matches);
            
            foreach ($matches[1] as $key => $value) {
                list($modName, $xslFile, $param1, $param2) = explode('&', $value);

                $hMod = new CModule($this->hDbConn, $modName, $xslFile, $param1, $param2);
                $sPage = str_replace($matches[0][$key], $hMod->execute(), $sPage);
    
                unset($hMod);
            }
            $this->sPageContent = $sPage;
        }
    }
    
    function getPageContent() {
        return $this->sPageContent;
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
    function oauth($type) {
        list($realHost,)=explode(':',$_SERVER['HTTP_HOST']);

        $cur_link = sprintf('https://%s/?page=%s',
            $realHost,
            $_GET['page']
        );
        
        $getUser = false;    
        
        switch ($type) {
            case 'vk':
                $params = array(
                    'client_id' => VK_CLIENT_ID,
                    'client_secret' => VK_SECRET,
                    'code' => $_REQUEST['code'],
                    'redirect_uri' => $cur_link
                );

                $token = json_decode(@file_get_contents('https://oauth.vk.com/access_token'.
                    '?'.http_build_query($params)), true);
            
                if (isset($token['access_token'])) {
                    $params = array(
                        'uids'         => $token['user_id'],
                        'fields'       => 'uid,first_name,photo_50',
                        'access_token' => $token['access_token']
                    );
            
                    $userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get'.
                        '?'.http_build_query($params)), true);
                    if (isset($userInfo['response'][0]['uid'])) {
                        $userInfo = $userInfo['response'][0];
                        $userInfo['type'] = 'vk';
                        $getUser = true;
                    }
                }
                break;
            case 'fb':
                $params = array(
                    'client_id'     => FB_CLIENT_ID,
                    'client_secret' => FB_SECRET,
                    'code'          => $_REQUEST['code'],
                    'redirect_uri'  => $cur_link
                );
                
                $str = parse_str(@file_get_contents('https://graph.facebook.com/oauth/access_token'.
                    '?'.http_build_query($params)));
            
                if (isset($access_token)) {
                    $uInfo = json_decode(
                        file_get_contents(
                            sprintf('https://graph.facebook.com/me?fields=id,first_name,picture&access_token=%s',
                                $access_token)
                        ), true
                    );
                    
                     if (isset($uInfo['id'])) {
                        $userInfo['uid'] = $uInfo['id'];
                        $userInfo['first_name'] = $uInfo['first_name'];
                        $userInfo['photo_50'] = $uInfo['picture']['data']['url'];
                        $userInfo['type'] = 'fb';
                        $getUser = true;
                    }
                }
                break;
            case 'gl':
                $params = array(
                    'client_id'     => GL_CLIENT_ID,
                    'client_secret' => GL_SECRET,
                    'code'          => $_REQUEST['code'],
                    'redirect_uri'  => $cur_link,
                    'grant_type'    => 'authorization_code'
                );
                
                $url = 'https://accounts.google.com/o/oauth2/token';
                
                $options = array(
                    'http' => array(
                        'header'  => 'Content-type:application/x-www-form-urlencoded\r\n',
                        'method'  => 'POST',
                        'content' => http_build_query($params)
                    )
                );
                $context  = stream_context_create($options);
                $result = @file_get_contents($url, false, $context);
                if ($result === FALSE) {
                	$error = error_get_last();
                    echo 'Can\'t get Google permissions!';
                    echo $error['message'];
                    exit;
                }
                $access_token = json_decode($result, true)['access_token'];
                
                $result = file_get_contents(sprintf('https://www.googleapis.com/oauth2/v1/userinfo?access_token=%s', $access_token));
                    
                if ($result) {
                    $uInfo = json_decode($result, true);

                    if (isset($uInfo['id'])) {
                        $userInfo['uid'] = $uInfo['id'];
                        $userInfo['first_name'] = $uInfo['given_name'];
                        $userInfo['photo_50'] = $uInfo['picture'].'?sz=50';
                        $userInfo['type'] = 'gl';
                        $getUser = true;
                    }
                }
                break;
        }
        if ($getUser) {
            $_SESSION['user']['id'] = $userInfo['uid'];
            $_SESSION['user']['type'] = $userInfo['type'];
            $_SESSION['user']['name'] = $userInfo['first_name'];
            $_SESSION['user']['img'] = $userInfo['photo_50'];
            
            
            $loc = $_SESSION['user_referer'];
            unset($_SESSION['user_referer']);
            header("Location: $loc");
        }
    }
    
    function userLogout() {
        unset($_SESSION['user']);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
    function commentAdd() {
        if (isset($_POST['user_id'])) {
            if (!filter_var($_POST['unit_id'], FILTER_VALIDATE_INT)) {
                echo 'Wrong parameters were passed!';
            }
            else {        
                $q = "INSERT 
                		INTO comments (
                			unit_id,
                			user_id,
                			p_com_id,
                			type,
                			name,
                			comment
                		) 
                		VALUES (%d,'%s',%s,'%s','%s','%s')";
                $q = sprintf($q,
                    $_POST['unit_id'],
                    $_POST['user_id'],
                    filter_var($_POST['p_com_id'], FILTER_VALIDATE_INT) ? $_POST['p_com_id'] : 'NULL',
                    $_POST['type'],
                    $this->hDbConn->real_escape_string($_SESSION['user']['name']),
                    $this->hDbConn->real_escape_string($_POST['comment'])
                );
                $this->hDbConn->exec($q);
            }
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
    // generator of sitexml.xml
    function sitemap() {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $urlset = $xml->createElement('urlset');
        $urlset = $xml->appendChild($urlset);
        $attr = $xml->createAttribute('xmlns');
        $attr->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $urlset->appendChild($attr);

        $q = 'SELECT date FROM units ORDER BY date LIMIT 1';
        $res = $this->hDbConn->query($q);
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

        $q = 'SELECT id,date FROM units';
        $res = $this->hDbConn->query($q);
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
        
        header('Content-type: application/xml');
        echo $xml->saveXML();
    }
    
    //-----------------------------------------------------
    // Admin page functionality
    //-----------------------------------------------------    
    private function adminPage($act) {
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
            case 'admin_unit_form':
                if ($this->isAuth())
                    $this->setTemplate('tpl/admin_unit_form.tpl');
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unit_edit':
                if ($this->isAuth())
                    $this->editUnit();
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unit_add':
                if ($this->isAuth())
                    $this->addUnit();
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');                
                break;
            case 'unit_del':
                if ($this->isAuth())
                    $this->deleteUnit($_GET['id']);
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unit_arch':
                if ($this->isAuth())
                    $this->archUnit($_GET['id']);
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'main':
                if ($this->isAuth())
                    $this->setTemplate('tpl/admin.tpl');
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'unapproved_comments':
                if ($this->isAuth())
                    $this->setTemplate('tpl/admin_unapproved_comments_list.tpl');
                else
                    header('Location: ?page=admin&act=login_form&msg=access_denied');
                break;
            case 'login_form':
            default:
                if ($this->isAuth()) {
                    header('Location: ?page=admin&act=main');
                }
                else {
                    $this->setTemplate('tpl/admin_login.tpl');
                }                    
                break;
        }        
    }
    
    function addUnit() {
        $aImportantInt = array(
            $_POST['category'], 
            $_POST['city'], 
            $_POST['manufacturer']
        );

        if ($this->varValid($aImportantInt, FILTER_VALIDATE_INT)) {
            $stmt = $this->hDbConn->prepare('
            	INSERT 
				INTO units(
					cat_id,
					city_id,
					manufacturer_id,
					name,
					description,
					price,
					year,
					mileage,
					op_time
				)
				VALUES (
					:cat_id,
					:city_id,
					:manufacturer_id,
					:name,
					:description,
					:price,
					:year,
					:mileage,
					:op_time
				)'
			);
            $stmt->bindValue(':cat_id', $_POST['category'], \PDO::PARAM_INT);
            $stmt->bindValue(':city_id', $_POST['city'], \PDO::PARAM_INT);    
            $stmt->bindValue(':manufacturer_id', $_POST['manufacturer'], \PDO::PARAM_INT);    
            $stmt->bindValue(':name', $_POST['name'], \PDO::PARAM_STR);
            $stmt->bindValue(':description', $_POST['description'], \PDO::PARAM_STR);
            $stmt->bindValue(':price', $_POST['price'], \PDO::PARAM_INT);
            $stmt->bindValue(':year', $_POST['year'], \PDO::PARAM_INT);
            $mileage = ctype_digit($_POST['mileage']) ? $_POST['mileage'] : 'NULL';
            $stmt->bindValue(':mileage', $mileage, \PDO::PARAM_STR);
            $op_time =  ctype_digit($_POST['op_time']) ? $_POST['op_time'] : 'NULL';
            $stmt->bindValue(':op_time', $op_time, \PDO::PARAM_STR);
            $stmt->execute();
            $id = $this->hDbConn->lastInsertId();
    
            $stmt = $this->hDbConn->prepare('
            	INSERT
				INTO images (
					unit_id,
					img,
					`order`
				) 
				VALUES (
					:uid,
					:img,
					:ord
				)'
			);
            $stmt->bindValue(':uid', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':img', $img, \PDO::PARAM_STR);
            $stmt->bindParam(':ord', $ord, \PDO::PARAM_INT);
            
            for ($i = 0; $i < count($_POST['images']); $i++) {
                rename('tmp_images/'.$_POST['images'][$i], 'images/'.$_POST['images'][$i]);
                rename('tmp_images/tmb/'.$_POST['images'][$i], 'images/tmb/'.$_POST['images'][$i]);
                
                $img = $_POST['images'][$i];
                $ord = $i + 1;
                
                $stmt->execute();
            }
            header('Location: ?page=admin&act=main');
        }
        else {
            echo 'Wrong data have been passed!';
        }       
    }
    
    function editUnit() {
        $aImportantInt = array(
            $_POST['category'], 
            $_POST['city'], 
            $_POST['manufacturer'],
            $_POST['id']
        );

        if ($this->varValid($aImportantInt, FILTER_VALIDATE_INT)) {
            $stmt = $this->hDbConn->prepare('
            	UPDATE units 
				SET 
					cat_id=:cat_id,
					city_id=:city_id,
					manufacturer_id=:manufacturer_id,
					name=:name,
					description=:description,
					price=:price,year=:year,
					mileage=:mileage,
					op_time=:op_time 
				WHERE 
					id=:id'
			);
            $stmt->bindValue(':cat_id', $_POST['category'], \PDO::PARAM_INT);
            $stmt->bindValue(':city_id', $_POST['city'], \PDO::PARAM_INT);    
            $stmt->bindValue(':manufacturer_id', $_POST['manufacturer'], \PDO::PARAM_INT);    
            $stmt->bindValue(':name', $_POST['name'], \PDO::PARAM_STR);
            $stmt->bindValue(':description', $_POST['description'], \PDO::PARAM_STR);
            $stmt->bindValue(':price', $_POST['price'], \PDO::PARAM_INT);
            $stmt->bindValue(':year', $_POST['year'], \PDO::PARAM_INT);
            $mileage = ctype_digit($_POST['mileage']) ? $_POST['mileage'] : 'NULL';
            $stmt->bindValue(':mileage', $mileage, \PDO::PARAM_INT);
            $op_time =  ctype_digit($_POST['op_time']) ? $_POST['op_time'] : 'NULL';
            $stmt->bindValue(':op_time', $op_time, \PDO::PARAM_INT);
            $stmt->bindValue(':id', $_POST['id'], \PDO::PARAM_INT);
            $stmt->execute();

            // delete present images which were removed 
            if (isset($_POST['available_images'])) {
                foreach ($_POST['available_images'] as $available_image) {        
                    if (array_search($available_image, $_POST['images']) === FALSE) {
                        unlink('images/'.$available_image);
                        unlink('images/tmb/'.$available_image);
                    }
                }
            }
            else
                $_POST['available_images'] = array();
                
            
            $stmt = $this->hDbConn->prepare('
            	DELETE 
				FROM images 
				WHERE unit_id=:id'
			);
            $stmt->bindValue(':id', $_POST['id'], \PDO::PARAM_INT);
            $stmt->execute();
            
            $stmt = $this->hDbConn->prepare('
            	INSERT 
				INTO images (
					unit_id,
					img,
					`order`
				) 
				VALUES (
					:uid,
					:img,
					:ord)'
			);
            $stmt->bindValue(':uid', $_POST['id'], \PDO::PARAM_INT);
            $stmt->bindParam(':img', $img, \PDO::PARAM_STR);
            $stmt->bindParam(':ord', $ord, \PDO::PARAM_INT);
            
            for ($i = 0; $i < count($_POST['images']); $i++) {
                if (array_search($_POST['images'][$i],
                		$_POST['available_images']) === FALSE) {
                    rename('tmp_images/'.$_POST['images'][$i], 
                    	'images/'.$_POST['images'][$i]);
                    rename('tmp_images/tmb/'.$_POST['images'][$i],
                    	'images/tmb/'.$_POST['images'][$i]);
                }
                $img = $_POST['images'][$i];
                $ord = $i + 1;
                
                $stmt->execute();                
            }
            
            header('Location: ?page=admin&act=main');     
        }
        else {
            echo 'Wrong data have been passed!';
        }
    }
    
    function deleteUnit($u_id) {
        $q = sprintf('SELECT img 
        				FROM images 
        				WHERE unit_id=%d',
        				$u_id);
        $imagesRes = $this->hDbConn->query($q);
        while ($ir = $imagesRes->fetch(\PDO::FETCH_ASSOC)) {
            unlink('/images/tbm/'.$ir['name']);
            unlink('/images/'.$ir['name']);
        }
        $q = sprintf('DELETE FROM images WHERE unit_id=%d', $u_id);
        $this->hDbConn->exec($q);
        $q = sprintf('DELETE FROM units WHERE id=%d', $u_id);
        $this->hDbConn->exec($q);
            
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
    function archUnit($u_id) {
        $q = sprintf('UPDATE units SET is_arch=TRUE WHERE id=%d', $u_id);
        $this->hDbConn->exec($q);
            
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    

    //-----------------------------------------------------
    // Ajax page functionality
    //-----------------------------------------------------    
    private function ajaxPage($mode) {
        switch ($mode) {
            case 'city':
                $json = array();
                $q = sprintf('SELECT 
                					id,
                					name 
                				FROM `cities` 
                				WHERE `rd_id` IN (
                					SELECT id 
                					FROM `regions` 
                					WHERE fd_id=%d
                				) 
                				ORDER BY name', 
                    filter_var($_GET['fdid'], FILTER_SANITIZE_NUMBER_INT)
                );
                if ($res = $this->hDbConn->query($q)) {
                    while ($r = $res->fetch(\PDO::FETCH_ASSOC)) {
                        $json[] = array(
                            'id' => $r['id'],
                            'name' => $r['name']
                        );
                    }
                }
                $this->sPageContent = json_encode($json);
                break;
            case 'image_load':
                if ($_FILES['afile']['error'] === UPLOAD_ERR_OK ) {
                    $fName = uniqid() . '.jpeg';
                
                    $img = $this->resizeImage($_FILES['afile']['tmp_name'], 1280, 1024);
                    $tmb = $this->resizeImage($_FILES['afile']['tmp_name'], 190, 190, TRUE);
                
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
                    $this->sPageContent = $json;
                }
                break;
        }
    }

}

?>