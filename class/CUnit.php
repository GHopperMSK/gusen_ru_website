<?php
namespace gusenru;
use \ForceUTF8\Encoding;

class CUnit
{
	private $id = NULL;
	private $is_arch;
	private $owner_id;
	private $owner;
	private $name;
	private $description;
	private $price;
	private $year;
	private $mileage;
	private $op_time;
	private $city = array(
		'id'				=> NULL,
		'name'				=> NULL,
		'reg_id'			=> NULL,
		'region'			=> NULL,
		'fdist_id'			=> NULL,
		'fdistrict'			=> NULL,
		'fdistrict_short'	=> NULL
	);
	private $cat_id;
	private $category;
	private $manuf_id;
	private $manufacturer;
	private $img = array();
	
	private $int_params = array(
		'id','price','year','mileage','op_time','cat_id','manuf_id','owner_id'
	);
	private $int_city_params = array(
		'id','reg_id','fdist_id'
	);

	private $_link;
	private $_catLink;
	
	function __construct($id=NULL) {
		CWebPage::debug("CUnit::__construct({$id})");

        if (!empty($id)) {
        	$this->id = $id;
        	$this->fillUnitData();
        }
    }

    function __set($name, $val) {
    	if (property_exists($this, $name)) {
    		if (in_array($name, $this->int_params)) {
    			$val = filter_var( $val, FILTER_SANITIZE_NUMBER_INT);
    		}
    		elseif ($name == 'img') {
    			// TODO: check if each file exists
    			for ($i=0; $i<count($val); $i++) {
    				$val[$i] = trim($val[$i]);
    			}
    		}
    		else
    			$val = trim($val);
	    	$this->$name = $val;
    	}
    	else
    		throw new \Exception("Member 'CUnit::{$name}' doesn't exists!");
    }
    
    function __get($name) {
    	if (property_exists($this, $name)) {
    		if (!empty($this->$name)) {
	    		if (!in_array($name, $this->int_params) && ($name != 'img'))
	    			$val = htmlspecialchars($this->$name);
				else
	    			$val = $this->$name;
    		}
    		else {
    			if ($name == 'img')
    				$val = array();
    			else
    				$val = FALSE;
    		}
    		return $val;
    	}
    	else
    		throw new \Exception("Member 'CUnit::{$name}' doesn't exists!");
    }
    
	function __isset($name) {
    	if (property_exists($this, $name)) {
    		return isset($this->$name);
    	}
    	else
    		throw new \Exception("Member 'CUnit::{$name}' doesn't exists!");
    }    
    
    function getCityParam($name) {
    	if (array_key_exists($name, $this->city)) {
    		if (!in_array($name, $this->int_city_params)) {
    			$val = htmlspecialchars($this->city[$name]);
    		}
    		else
    			$val = $this->city[$name];
    		
	    	return $val; 
    	}
    	else
    		throw new \Exception("Member CUnit::city['{$name}'] doesn't exists!");
    }

    function setCityParam($name, $val) {
    	if (array_key_exists($name, $this->city)) {
    		if (in_array($name, $this->int_city_params)) {
    			$val = filter_var( $val, FILTER_SANITIZE_NUMBER_INT);
    		}
    		else
    			$val = trim($val);
    		
    		$this->city[$name] = $val;
    	}
    	else
    		throw new \Exception("Member CUnit::city['{$name}'] doesn't exists!");
    }
    
    function fillUnitData() {
    	$hDbConn = CDataBase::getInstance();
    	if ($this->id) {
	        $q = '
	        	SELECT
					u.id AS id,
					u.owner_id,
					owners.name AS owner,
					u.name AS name,
					u.description AS description,
					u.price AS price,
					u.year AS year,
					u.mileage AS mileage,
					u.op_time AS op_time,
					u.is_arch,
					cities.id AS city_id,
					cities.name AS city,
					regions.id AS region_id,
					regions.name AS region,
					fdistricts.id AS fd_id,
					fdistricts.name AS fdistrict,
					fdistricts.short_name AS fdistrict_short,
					categories.id AS cat_id,
					categories.name AS category,
					manufacturers.id AS manufacturer_id,
					manufacturers.name AS manufacturer,
					(SELECT img 
						FROM images i 
						WHERE i.unit_id=u.id 
						ORDER BY `ORDER` ASC
						LIMIT 1) as img
				FROM units u
				JOIN owners ON u.owner_id = owners.id
				JOIN cities ON u.city_id = cities.id
				JOIN regions ON cities.rd_id = regions.id
				JOIN fdistricts ON regions.fd_id = fdistricts.id
				JOIN categories ON u.cat_id = categories.id
				JOIN manufacturers ON manufacturers.id = u.manufacturer_id
				WHERE u.id=%d';
	
	        $q = sprintf($q, $this->id);
	        $res = $hDbConn->query($q);
	        $ur = $res->fetch(\PDO::FETCH_ASSOC);

			if (MOD_REWRITE)
				$this->_link = "/unit/{$this->id}";
			else
				$this->_link = "/?page=unit&id={$this->id}";

			$this->name = isset($ur['name']) ? $ur['name'] : NULL;
			$this->owner_id = $ur['owner_id'];
			$this->owner = $ur['owner'];
			$this->description = isset($ur['description']) ? 
				$ur['description']: NULL;
			$this->price = isset($ur['price']) ? $ur['price'] : NULL;
			$this->year = isset($ur['year']) ? $ur['year'] : NULL;
			$this->mileage = isset($ur['mileage']) ? $ur['mileage'] : NULL;
			$this->op_time = isset($ur['op_time']) ? $ur['op_time'] : NULL;
			$this->cat_id = isset($ur['cat_id']) ? $ur['cat_id'] : NULL;
			$this->category = isset($ur['category']) ? $ur['category'] : NULL;
			
			if ($this->cat_id) {
				$this->_catLink = MOD_REWRITE ? "/search/{$this->cat_id}"
					: "/?page=search&vType={$this->cat_id}";
			}
			
			$this->manuf_id = isset($ur['manufacturer_id']) ? 
				$ur['manufacturer_id']: NULL;
			$this->manufacturer= isset($ur['manufacturer']) ? 
				$ur['manufacturer'] : NULL;
			$this->is_arch = $ur['is_arch'];
	
			$this->city['id'] = $ur['city_id'];
			$this->city['name'] = $ur['city'];
			$this->city['reg_id'] = $ur['region_id'];
			$this->city['region'] = $ur['region'];
			$this->city['fdist_id'] = $ur['fd_id'];
			$this->city['fdistrict'] = $ur['fdistrict'];
			$this->city['fdistrict_short'] = $ur['fdistrict_short'];
	
	        $q = "SELECT img 
	        		FROM images 
	        		WHERE images.unit_id=%d
	        		ORDER BY `order`";
	
	        $q = sprintf($q, $this->id);
	        $res = $hDbConn->query($q);
	        while ($ir = $res->fetch(\PDO::FETCH_ASSOC)) {
	        	$this->img[] = $ir['img'];
	        }
    	}
    }
    
    function jsonData() {
    	$max_len = 600;
    	
    	$aDescr = array();
    	$aDescr['category'] = $this->category;
    	$aDescr['manufacturer'] = $this->manufacturer;
    	$aDescr['name'] = $this->name;
    	$aDescr['year'] = $this->year;
    	$aDescr['fdistrict'] = $this->city['fdistrict'];
    	$aDescr['region'] = $this->city['region'];
    	$aDescr['city'] = $this->city['name'];
    	if (strlen($this->description) > $max_len)
			$aDescr['description'] = mb_substr($this->description, 0, $max_len).'...';
		else
			$aDescr['description'] = $this->description;
		
		foreach ($aDescr as $key => $value) {
			$aDescr[$key] = Encoding::toUTF8($value);
		}
		
    	return json_encode($aDescr);
    }
    
    function getDescription() {
    	$sDescr = "{$this->category} / {$this->manufacturer} ".
    		"{$this->name}, {$this->year} г.в. ".
    		"{$this->city['fdistrict']}, {$this->city['region']},г. ".
    		"{$this->city['name']}. Спецтехника б/у, продажа от ".
    		"собственника, конкурентная цена, лизинг.";
    	return $sDescr;
    }
    
    /**
     * Returns array of user data
     * 
     * @return array
     */
    function get() {
        $aUnit = array();
        $aUnit['unit']['@attributes'] = array(
        	'id' => $this->id,
        	'name' => $this->name,
        	'is_arch' => $this->is_arch ? 'TRUE' : 'FALSE'
        );
        $aUnit['unit']['link'] = $this->_link;
        $aUnit['unit']['owner']['@content'] = $this->owner;
        $aUnit['unit']['owner']['@attributes'] = array(
        	'id' => $this->owner_id
    	);
    	$aUnit['unit']['description'] = $this->description;
    	$aUnit['unit']['price'] = $this->price;
    	$aUnit['unit']['year'] = $this->year;
    	
    	$aUnit['unit']['category']['@content'] = $this->category;
    	$aUnit['unit']['category']['@attributes'] = array(
        	'id' => $this->cat_id,
        	'link' => $this->_catLink
    	);
    	
    	$aUnit['unit']['fdistrict']['@content'] = $this->city['fdistrict'];
    	$aUnit['unit']['fdistrict']['@attributes'] = array(
        	'id' => $this->city['fdist_id'],
        	'short' => $this->city['fdistrict_short']
    	);
    	$aUnit['unit']['region']['@content'] = $this->city['region'];
    	$aUnit['unit']['region']['@attributes'] = array(
        	'id' => $this->city['reg_id']
    	);
    	$aUnit['unit']['city']['@content'] = $this->city['name'];
    	$aUnit['unit']['city']['@attributes'] = array(
        	'id' => $this->city['id']
    	);
    	$aUnit['unit']['manufacturer']['@content'] = $this->manufacturer;
    	$aUnit['unit']['manufacturer']['@attributes'] = array(
        	'id' => $this->manuf_id
    	);
    	if (isset($this->mileage)) {
    		$aUnit['unit']['mileage'] = $this->mileage;
    	}
    	if (isset($this->op_time)) {
    		$aUnit['unit']['op_time'] = $this->op_time;
    	}
    	$aUnit['unit']['images'] = array();
    	
    	for ($i=0;$i<count($this->img);$i++) {
    		$aUnit['unit']['images']["image{$i}"] = $this->img[$i];
    	}
		return $aUnit;    	
    }
    
    function addUnit() {
    	$hDbConn = CDataBase::getInstance();
        $stmt = $hDbConn->prepare('
        	INSERT 
			INTO units(
				owner_id,
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
				:owner_id,
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
        $stmt->bindValue(':owner_id', $this->owner_id, \PDO::PARAM_INT);
        $stmt->bindValue(':cat_id', $this->cat_id, \PDO::PARAM_INT);
        $stmt->bindValue(':city_id', $this->city['id'], \PDO::PARAM_INT);    
        $stmt->bindValue(':manufacturer_id', $this->manuf_id, \PDO::PARAM_INT);    
        $stmt->bindValue(':name', $this->name, \PDO::PARAM_STR);
        $stmt->bindValue(':description', $this->description, \PDO::PARAM_STR);
        $stmt->bindValue(':price', $this->price, \PDO::PARAM_INT);
        $stmt->bindValue(':year', $this->year, \PDO::PARAM_INT);
        $mileage = empty($this->mileage) ? NULL : $this->mileage;
        $stmt->bindValue(':mileage', $mileage, \PDO::PARAM_STR);
        $op_time = empty($this->op_time) ? NULL : $this->op_time;
        $stmt->bindValue(':op_time', $op_time, \PDO::PARAM_STR);
        $stmt->execute();
        $this->id = $hDbConn->lastInsertId();

        $stmt = $hDbConn->prepare('
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
        $stmt->bindValue(':uid', $this->id, \PDO::PARAM_INT);
        $stmt->bindParam(':img', $img, \PDO::PARAM_STR);
        $stmt->bindParam(':ord', $ord, \PDO::PARAM_INT);
        
        for ($i = 0; $i < count($this->img); $i++) {
            rename("tmp_images/{$this->img[$i]}", "images/{$this->img[$i]}");
            rename("tmp_images/tmb/{$this->img[$i]}", "images/tmb/{$this->img[$i]}");
            
            $img = $this->img[$i];
            $ord = $i + 1;
            
            $stmt->execute();
        } 
    }
    
    function editUnit($available_images = array()) {
    	$hDbConn = CDataBase::getInstance();
        $stmt = $hDbConn->prepare('
        	UPDATE units 
			SET 
				owner_id=:owner_id,
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
		
		$stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->bindValue(':name', $this->name, \PDO::PARAM_STR);
        $stmt->bindValue(':owner_id', $this->owner_id, \PDO::PARAM_INT);
        $stmt->bindValue(':description', $this->description, \PDO::PARAM_STR);
        $stmt->bindValue(':cat_id', $this->cat_id, \PDO::PARAM_INT);
        $stmt->bindValue(':city_id', $this->city['id'], \PDO::PARAM_INT);    
        $stmt->bindValue(':manufacturer_id', $this->manuf_id, \PDO::PARAM_INT);    
        $stmt->bindValue(':price', $this->price, \PDO::PARAM_INT);
        $stmt->bindValue(':year', $this->year, \PDO::PARAM_INT);
        $mileage = empty($this->mileage) ? NULL : $this->mileage;
        $stmt->bindValue(':mileage', $mileage, \PDO::PARAM_STR);
        $op_time =  empty($this->op_time) ? NULL : $this->op_time;
        $stmt->bindValue(':op_time', $op_time, \PDO::PARAM_STR);
        
        $stmt->bindValue(':id', $_POST['id'], \PDO::PARAM_INT);
        $stmt->execute();

        // delete present images which were removed 
        foreach ($available_images as $available_image) {        
            if (array_search($available_image, $this->img) === FALSE) {
            	$aPath = array('images', 'images/tmb');
            	foreach ($aPath as $path) {
            		$img = "{$path}/{$available_image}";
	            	if (file_exists($img)) {
	                	unlink($img);
	            	}
	            	else
	            		throw new \Exception("File $img doesn't exists!");
            	}
            }
        }
            
        $stmt = $hDbConn->prepare('
        	DELETE 
			FROM images 
			WHERE unit_id=:id'
		);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        
        $stmt = $hDbConn->prepare('
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
        $stmt->bindValue(':uid', $this->id, \PDO::PARAM_INT);
        $stmt->bindParam(':img', $img, \PDO::PARAM_STR);
        $stmt->bindParam(':ord', $ord, \PDO::PARAM_INT);
        
        for ($i = 0; $i < count($this->img); $i++) {
            if (array_search($this->img[$i], $available_images) === FALSE) {
            	$aPath = array('images', 'images/tmb');
            	foreach ($aPath as $path) {
            		$tmp_img = "tmp_{$path}/{$this->img[$i]}";
            		$img = "{$path}/{$this->img[$i]}";
	            	if (file_exists($tmp_img)) {
	                	if (!rename($tmp_img, $img))
	                		throw new \Exception("Can't rename {$tmp_img} file!");
	            	}
	            	else
	            		throw new \Exception("File $img doesn't exists!");
            	}
            }
            
            $img = $this->img[$i];
            $ord = $i + 1;
            
            $stmt->execute();                
        }
    }
    
    static function deleteUnit($uid) {
    	$hDbConn = CDataBase::getInstance();
        $q = sprintf('SELECT img 
        				FROM images 
        				WHERE unit_id=%d',
        				$u_id);
        $res = $hDbConn->query($q);
        while ($ir = $res->fetch(\PDO::FETCH_ASSOC)) {
        	$aPath = array('images', 'images/tmb');
        	foreach ($aPath as $path) {
        		$img = "{$path}/{$ir['img']}";
            	if (file_exists($img)) {
                	unlink($img);
            	}
            	else
            		throw new \Exception("File $img doesn't exists!");
        	}
        }
        $q = sprintf('DELETE FROM images WHERE unit_id=%d', $uid);
        $hDbConn->exec($q);
        $q = sprintf('DELETE FROM units WHERE id=%d', $uid);
        $hDbConn->exec($q);
    }
	
	static function archUnit($uid) {
        $q = sprintf('UPDATE units SET is_arch=TRUE WHERE id=%d', $uid);
        CDataBase::getInstance()->exec($q);
	}
	
	static function restoreUnit($uid) {
        $q = sprintf('UPDATE units SET is_arch=FALSE WHERE id=%d', $uid);
        CDataBase::getInstance()->exec($q);
	}
}
?>
