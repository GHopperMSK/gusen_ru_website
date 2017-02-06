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
    	CWebPage::debug("CPaginator::__construct({$sLinkPattern},".
    		"{$iUnitsTotal},{$iUnitsOnPage},{$iCurPage},{$iNavShowPages})");
    	
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
    
	/**
	 * Return DOMElement object with pagination elements:
	 *  <paginator>
	 *     <page type="first">
	 *         <number>1</number>
	 *         <link>?page=search&offset=1</link>
	 *     </page>
	 *     <page type="prev">
	 *         <number>3</number>
	 *         <link>?page=search&offset=3</link>
	 *     </page>
	 *     <page type="regular">
	 *         <number>3</number>
	 *         <link>?page=search&offset=10</link>
	 *      </page>
	 *     <page type="current">
	 *         <number>4</number>
	 *         <link>?page=search&offset=11</link>
	 *      </page>
	 *      ...
	 *     <page type="next">
	 *         <number>5</number>
	 *         <link>?page=search&offset=5</link>
	 *      </page>
	 *     <page type="last">
	 *         <number>68</number>
	 *         <link>?page=search&offset=68</link>
	 *      </page>
	 * </paginator>
	 */
    function getXML() {
        $xmlDoc = new \DOMDocument('1.0', 'utf-8');
        $ePaginator = $xmlDoc->createElement('paginator');
        $ePaginator = $xmlDoc->appendChild($ePaginator);

        $iPagesTotal = ceil($this->iUnitsTotal/$this->iUnitsOnPage);

        if ($iPagesTotal>1) {
            $iStartPage = max($this->iCurPage-(($this->iNavShowPages-1)/2), 1);
            $iEndPage = $iStartPage + $this->iNavShowPages - 1;
            if ($iEndPage > $iPagesTotal) {
                $iEndPage = $iPagesTotal;
                $iStartPage = max($iEndPage - $this->iNavShowPages + 1, 1);
            }
            if ($iStartPage > 1) {
                $ePage = $xmlDoc->createElement('page');
                $ePage = $ePaginator->appendChild($ePage);
                $eNumber = $xmlDoc->createElement('number', 1);
                $ePage->appendChild($eNumber);
                $eLink = $xmlDoc->createElement('link', sprintf($this->sLinkPattern, 1));
                $ePage->appendChild($eLink);
                $eIsCurrent = $xmlDoc->createAttribute('type');
                $eIsCurrent->value = 'first';
                $ePage->appendChild($eIsCurrent);
            }
            if ($this->iCurPage > 1) {
                $ePage = $xmlDoc->createElement('page');
                $ePage = $ePaginator->appendChild($ePage);
                $eNumber = $xmlDoc->createElement('number', $this->iCurPage - 1);
                $ePage->appendChild($eNumber);
                $eLink = $xmlDoc->createElement('link', sprintf($this->sLinkPattern, $this->iCurPage - 1));
                $ePage->appendChild($eLink);
                $eIsCurrent = $xmlDoc->createAttribute('type');
                $eIsCurrent->value = 'prev';
                $ePage->appendChild($eIsCurrent);
            }
            for ($i=$iStartPage; $i<=$iEndPage; $i++) {
                $ePage = $xmlDoc->createElement('page');
                $ePage = $ePaginator->appendChild($ePage);
                $eNumber = $xmlDoc->createElement('number', $i);
                $ePage->appendChild($eNumber);
                $eLink = $xmlDoc->createElement('link', sprintf($this->sLinkPattern, $i));
                $ePage->appendChild($eLink);
                $eIsCurrent = $xmlDoc->createAttribute('type');
                if ($i == $this->iCurPage) {
                    $eIsCurrent->value = 'current';
                } else {
                    $eIsCurrent->value = 'regular';
                }
                $ePage->appendChild($eIsCurrent);
            }
            if ($this->iCurPage < $iEndPage) {
                $ePage = $xmlDoc->createElement('page');
                $ePage = $ePaginator->appendChild($ePage);
                $eNumber = $xmlDoc->createElement('number', $this->iCurPage + 1);
                $ePage->appendChild($eNumber);
                $eLink = $xmlDoc->createElement('link', sprintf($this->sLinkPattern, $this->iCurPage + 1));
                $ePage->appendChild($eLink);
                $eIsCurrent = $xmlDoc->createAttribute('type');
                $eIsCurrent->value = 'next';
                $ePage->appendChild($eIsCurrent);
            }
            if ($iEndPage < $iPagesTotal) {
                $ePage = $xmlDoc->createElement('page');
                $ePage = $ePaginator->appendChild($ePage);
                $eNumber = $xmlDoc->createElement('number', $iPagesTotal);
                $ePage->appendChild($eNumber);
                $eLink = $xmlDoc->createElement('link', sprintf($this->sLinkPattern, $iPagesTotal));
                $ePage->appendChild($eLink);
                $eIsCurrent = $xmlDoc->createAttribute('type');
                $eIsCurrent->value = 'last';
                $ePage->appendChild($eIsCurrent);
            }
        }
        return $ePaginator;
    }
}

?>