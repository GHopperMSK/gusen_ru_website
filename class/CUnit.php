<?php
namespace gusenru;

class CUnit
{
	private $id = null;
	private $name;
	private $description;
	private $price;
	private $year;
	private $mileage;
	private $op_time;
	private $city = array();
	private $cat_id;
	private $category;
	private $manuf_id;
	private $manufacturer;
	private $img = array();
	
	private $hDbConn = null;
	
	function __construct(&$hDbConn, $id=NULL) {
        if ($hDbConn instanceof CDataBase) {
            $this->hDbConn = $hDbConn;
        }
        else {
            echo 'Wrong CDataBase connection was passed!';
            exit;
        }
        if ($id) {
        	$this->id = $id;
        	$this->fillUnitData();
        }
    }
    
    function isSetParam($name) {
    	if (isset($this->$name))
    		return TRUE;
    	else
    		return FALSE;
    }
    
    function getParam($name) {
    	return $this->$name;
    }
    
    function getCityParam($name) {
    	return $this->city[$name];
    }
    
    function fillUnitData() {
    	if ($this->id) {
	        $q = '
	        	SELECT 
					u.id AS id,
					u.name AS name,
					u.description AS description,
					u.price AS price,
					u.year AS year,
					u.mileage AS mileage,
					u.op_time AS op_time,
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
				JOIN cities ON u.city_id=cities.id
				JOIN regions ON cities.rd_id=regions.id
				JOIN fdistricts ON regions.fd_id=fdistricts.id
				JOIN categories ON u.cat_id=categories.id
				JOIN manufacturers ON manufacturers.id=u.manufacturer_id
				WHERE u.id=%d';
	
	        $q = sprintf($q, $this->id);
	        $res = $this->hDbConn->query($q);
	        $ur = $res->fetch(\PDO::FETCH_ASSOC);
	        
			$this->name = isset($ur['name']) ? $ur['name'] : NULL;
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
        $topAttr->value = htmlentities($this->id);
        $top->appendChild($topAttr);
        $topAttr = $xmlDoc->createAttribute('name');
        $topAttr->value = htmlentities($this->name);
        $top->appendChild($topAttr);

        $sub = $xmlDoc->createElement('description',
            htmlentities($this->descrtiprion));
        $top->appendChild($sub);
        $sub = $xmlDoc->createElement('price',
            htmlentities($this->price));
        $top->appendChild($sub);
        $sub = $xmlDoc->createElement('year',
            htmlentities($this->year));
        $top->appendChild($sub);

        $sub = $xmlDoc->createElement('category',
            htmlentities($this->category));
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = htmlentities($this->cat_id);
        $sub->appendChild($subAttr);
        $top->appendChild($sub);
        
        $sub = $xmlDoc->createElement('fdistrict',
            htmlentities($this->city['fdistrict']));
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = htmlentities($this->city['fdist_id']);
        $sub->appendChild($subAttr);
        $subAttr = $xmlDoc->createAttribute('short');
        $subAttr->value = htmlentities($this->city['fdistrict_short']);
        $sub->appendChild($subAttr);
        $top->appendChild($sub);
  
        $sub = $xmlDoc->createElement('region',
            htmlentities($this->city['region']));
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = htmlentities($this->city['reg_id']);
        $sub->appendChild($subAttr);
        $top->appendChild($sub);
  
        $sub = $xmlDoc->createElement('city',
            htmlentities($this->city['name']));
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = htmlentities($this->city['id']);
        $sub->appendChild($subAttr);
        $top->appendChild($sub);

        $sub = $xmlDoc->createElement('manufacturer',
            htmlentities($this->manufacturer));
        $subAttr = $xmlDoc->createAttribute('id');
        $subAttr->value = htmlentities($this->manuf_id);
        $sub->appendChild($subAttr);
        $top->appendChild($sub);

        if ($this->isSetParam('mileage')) {
            $sub = $xmlDoc->createElement('mileage',
                htmlentities($this->mileage));
            $top->appendChild($sub);
        }
        if ($this->isSetParam('op_time')) {
            $sub = $xmlDoc->createElement('op_time',
                htmlentities($this->op_time));
            $top->appendChild($sub);
        }
        
        $eImages = $xmlDoc->createElement('images');
        $eImages = $top->appendChild($eImages);
        foreach ($this->img as $img) {

            $eImg = $xmlDoc->createElement('img', htmlentities($img));
            $eImages->appendChild($eImg);
        }

		return $top;
    }
	
}
?>
