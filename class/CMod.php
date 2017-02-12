<?php
namespace gusenru;

interface iModDriver
{
    public function execute();
}

class CMod implements iModDriver
{
	protected $_aContent = array();

	protected $_param1;
	protected $_param2;

    function __construct($param1, $param2) {
    	CWebPage::debug('CMod::__construct('.
    		(empty($param1) ? '' : ','.$param1).
    		(empty($param2) ? '' : ','.$param2).
    		')'
    	);

        $this->_param1 = $param1;
        $this->_param2 = $param2;
    }
    
    function __destruct () {
    	CWebPage::debug('CModule::__destruct()');
    	
        $this->_aContent = NULL;
    }
    
    protected function _addContent($aContent) {
    	$this->_aContent = array_merge($this->_aContent, $aContent);
    }

    function execute() {
    	return $this->_aContent;
    }
}

?>
