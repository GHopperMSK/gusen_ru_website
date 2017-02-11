<?php
namespace gusenru;

interface iModDriver
{
    public function execute();
}

class CModule implements iModDriver
{
	private $_hDriver = NULL;
	
	function __construct($modName, $param1, $param2) {
        // TODO: the Driver depends of modName. Driver loads with only two parameters
		$this->_hDriver = new CMod(
				$modName,
				$param1,
				$param2
		);        
/*
		if ($viewFile == 'null') {
			$this->_hDriver = new CTextDriver(
				$modName,
				$param1,
				$param2
			);
		}
		else {
			$this->_hDriver = new CXMLDriver(
				$modName,
				$viewFile,
				$param1,
				$param2
			);
		}
*/
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
