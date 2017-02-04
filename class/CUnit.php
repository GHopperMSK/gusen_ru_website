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
	
	private $hDbConn = null;
	
	function __construct(CDataBase $hDbConn, $id=NULL) {
		CWebPage::debug("CUnit::__construct({$id})");
		
        if ($hDbConn instanceof CDataBase) {
            $this->hDbConn = $hDbConn;
        }
        else {
        	throw new \Exception('Wrong CDataBase connection was passed!');
        }
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
	        $res = $this->hDbConn->query($q);
	        $ur = $res->fetch(\PDO::FETCH_ASSOC);
	        
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
	        $res = $this->hDbConn->query($q);
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
    		"собвтенника, конкурентная цена, лизинг.";
    	return $sDescr;
    }
    
    /**
     * Returns a DOMDocument with unit data
     * <?xml version="1.0" encoding="utf-8"?>
     * <root>
     *	<unit id="UNIT_ID" name="UNIT_NAME">
     *		<description>UNIT_DESCRIPTION</description>
     *		<price>UNIT_PRICE</price>
     *		<year>UNIT_YEAR</year>
     *		<category id="CAT_ID">UNIT_CATEGORY</category>
     *		<fdistrict id="FD_ID" short="УФО">Уральский федеральный округ</fdistrict>
     *		<region id="REG_ID">Свердловская обл.</region>
     *		<city id="CITY_ID">CITY_NAME</city>
     *		<manufacturer id="MANUF_ID">LIEBHERR</manufacturer>
     *		<images>
     *			<img>584da0e1f350c.jpeg</img>
     *			<img>584da0e225549.jpeg</img>
     *			<img>584da0e24bc40.jpeg</img>
     *		</images>
     *	</unit>
     * </root>
     * 
     */
    function getUnitDOM() {
        $xmlDoc = new \DOMDocument('1.0', 'utf-8');
        $eRoot = $xmlDoc->createElement('root');
        $eRoot = $xmlDoc->appendChild($eRoot);

        $top = $xmlDoc->createElement('unit');
        $top = $eRoot->appendChild($top);
        $topAttr = $xmlDoc->createAttribute('id');
        $topAttr->value = $this->id;
        $top->appendChild($topAttr);
        $topAttr = $xmlDoc->createAttribute('name');
        $topAttr->value = $this->name;
        $top->appendChild($topAttr);
        $topAttr = $xmlDoc->createAttribute('is_arch');
        $topAttr->value = $this->is_arch ? 'TRUE' : 'FALSE';
        $top->appendChild($topAttr);

        $sub = $xmlDoc->createElement('owner', $this->owner);
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = $this->owner_id;
        $sub->appendChild($subAttr);
        $top->appendChild($sub);

        $sub = $xmlDoc->createElement('description', $this->description);
        $top->appendChild($sub);
        $sub = $xmlDoc->createElement('price', $this->price);
        $top->appendChild($sub);
        $sub = $xmlDoc->createElement('year', $this->year);
        $top->appendChild($sub);

        $sub = $xmlDoc->createElement('category', $this->category);
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = $this->cat_id;
        $sub->appendChild($subAttr);
        $top->appendChild($sub);
        
        $sub = $xmlDoc->createElement('fdistrict', $this->city['fdistrict']);
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = $this->city['fdist_id'];
        $sub->appendChild($subAttr);
        $subAttr = $xmlDoc->createAttribute('short');
        $subAttr->value = $this->city['fdistrict_short'];
        $sub->appendChild($subAttr);
        $top->appendChild($sub);
  
        $sub = $xmlDoc->createElement('region', $this->city['region']);
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = $this->city['reg_id'];
        $sub->appendChild($subAttr);
        $top->appendChild($sub);
  
        $sub = $xmlDoc->createElement('city', $this->city['name']);
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = $this->city['id'];
        $sub->appendChild($subAttr);
        $top->appendChild($sub);

        $sub = $xmlDoc->createElement('manufacturer', $this->manufacturer);
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = $this->manuf_id;
        $sub->appendChild($subAttr);
        $top->appendChild($sub);

        if (isset($this->mileage)) {
            $sub = $xmlDoc->createElement('mileage', $this->mileage);
            $top->appendChild($sub);
        }
        if (isset($this->op_time)) {
            $sub = $xmlDoc->createElement('op_time', $this->op_time);
            $top->appendChild($sub);
        }
        
        $eImages = $xmlDoc->createElement('images');
        $eImages = $top->appendChild($eImages);
        foreach ($this->img as $img) {

            $eImg = $xmlDoc->createElement('img', $img);
            $eImages->appendChild($eImg);
        }

		return $top;
    }
    
    function addUnit() {
        $stmt = $this->hDbConn->prepare('
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
        $this->id = $this->hDbConn->lastInsertId();

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
        $stmt = $this->hDbConn->prepare('
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
            
        $stmt = $this->hDbConn->prepare('
        	DELETE 
			FROM images 
			WHERE unit_id=:id'
		);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
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
    
    static function deleteUnit($uid, CDataBase $hDbConn) {
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
	
	static function archUnit($uid, CDataBase $hDbConn) {
        $q = sprintf('UPDATE units SET is_arch=TRUE WHERE id=%d', $uid);
        $hDbConn->exec($q);
	}
	
	static function restoreUnit($uid, CDataBase $hDbConn) {
        $q = sprintf('UPDATE units SET is_arch=FALSE WHERE id=%d', $uid);
        $hDbConn->exec($q);		
	}
}
?>
