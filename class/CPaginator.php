<?php
namespace gusenru;

/**
 * 
 * @param string $sLinkPattern	// pattern of URL
 * @param int $iUnitsTotal		// the total number of units in pagination panel
 * @param int $iUnitsOnPage		// how much units are outputted on the page	
 * @param int $iCurPage			// the current page
 * @param int $iNavShowPages	// number of elements in the panel
 *
 * @return void
 */
class CPaginator {

    function __construct(
    		$sLinkPattern,
    		$iUnitsTotal,
    		$iUnitsOnPage,
    		$iCurPage,
    		$iNavShowPages) {
    	CWebPage::debug();
    	
    	$this->sLinkPattern = urldecode($sLinkPattern);
    	$this->iUnitsTotal = $iUnitsTotal;
    	$this->iUnitsOnPage = $iUnitsOnPage;
    	$this->iCurPage = $iCurPage;
    	
    	// must be odd due to symmetric
    	$this->iNavShowPages = max(
    		round($iNavShowPages, 0, PHP_ROUND_HALF_ODD),
    		3 // three is a minimum possible value
    	);
    }
    
  
    function get() {
    	$aPageNav = array();
    	
        $iPagesTotal = ceil($this->iUnitsTotal/$this->iUnitsOnPage);

        if ($iPagesTotal>1) {
            $iStartPage = max($this->iCurPage-(($this->iNavShowPages-1)/2), 1);
            $iEndPage = $iStartPage + $this->iNavShowPages - 1;
            if ($iEndPage > $iPagesTotal) {
                $iEndPage = $iPagesTotal;
                $iStartPage = max($iEndPage - $this->iNavShowPages + 1, 1);
            }
            if ($iStartPage > 1) {
            	$aPageNav['paginator']['page1']['number'] = 1;
            	$aPageNav['paginator']["page1"]['link'] =
            		sprintf($this->sLinkPattern, 1);
            	$aPageNav['paginator']['page1']['@attributes'] = array(
            		'type' => 'first'
        		);
                
            }
            if ($this->iCurPage > 1) {
            	$prev = $this->iCurPage - 1;
            	$aPageNav['paginator']["page{$prev}00"]['number'] = $prev;
            	$aPageNav['paginator']["page{$prev}00"]['link'] =
            		sprintf($this->sLinkPattern, $prev);
            	$aPageNav['paginator']["page{$prev}00"]['@attributes'] = array(
        			'type' => 'prev'
    			);
            }
            for ($i=$iStartPage; $i<=$iEndPage; $i++) {
            	$aPageNav['paginator']["page{$i}"]['number'] = $i;
            	$aPageNav['paginator']["page{$i}"]['link'] = 
            		sprintf($this->sLinkPattern, $i);
            	$aPageNav['paginator']["page{$i}"]['@attributes'] = array(
            		'type' => ($i == $this->iCurPage) ? 'current' : 'regular'
        		);
            }
            if ($this->iCurPage < $iEndPage) {
            	$next = $this->iCurPage + 1;
            	$aPageNav['paginator']["page{$next}00"]['number'] = $next;
            	$aPageNav['paginator']["page{$next}00"]['link'] =
            		sprintf($this->sLinkPattern, $next);
            	$aPageNav['paginator']["page{$next}00"]['@attributes'] = array(
            		'type' => 'next'
        		);
            }
            if ($iEndPage < $iPagesTotal) {
            	$aPageNav['paginator']["page{$iPagesTotal}"]['number'] = 
            		$iPagesTotal;
            	$aPageNav['paginator']["page{$iPagesTotal}"]['link'] =
            		sprintf($this->sLinkPattern, $iPagesTotal);
            	$aPageNav['paginator']
            			["page{$iPagesTotal}"]['@attributes'] = array(
            		'type' => 'last'
        		);
            }
        }
        return $aPageNav;
    }    
    
}

?>