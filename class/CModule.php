<?php
namespace gusenru;

class CModule
{
	private $_hDriver = NULL;
	
	function __construct($modKey, $param1, $param2) {
        $this->_param1 = $param1;
        $this->_param2 = $param2;

		$this->_setDriver($modKey);
	}
	
	private function _setDriver($key) {
		$mod = "gusenru\module\C{$key}";
		$this->_hDriver = new $mod (
				$this->_param1,
				$this->_param2
		);        
	}
	
	function execute() {
		if ($this->_hDriver instanceof iModDriver) {
			return $this->_hDriver->execute();
		}
		else
			throw new \Exception('CModule::execute can\'t define the Driver!');
	}
	
}

?>
