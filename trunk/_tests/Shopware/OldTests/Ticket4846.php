<?php
/**
 * Test default search
 * 1. Do a search and check for results
 * 2. Do a search for a blocked article and check result
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author st.hamann
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4846
 */
class Shopware_RegressionTests_Ticket4846 extends Enlight_Components_Test_Controller_TestCase
{
    /**
	 * Test default search
	 */
	public function testDefaultSearch()
    {
		$this->Front()->setParam('noViewRenderer', false);
		// Requires an existing article with "varian" in name
    	$this->dispatch('/search/search/sSearch/varian');
		$this->assertNotEmpty($this->View()->sSearchResults);
		$this->assertEquals(count($this->View()->sSearchResults),$this->View()->sSearchResultsNum);
		$this->reset();
	}

	/**
	 * Test default search - with blocking
	 * @return void
	 */
	public function testDefaultSearchWithBlocker()
    {
    	$this->Front()->setParam('noViewRenderer', false);
		// Requires an existing article with "gesperrt" in name and inactive for default customergroup
    	$this->dispatch('/search/search/sSearch/gesperrt');
		$this->assertEmpty($this->View()->sSearchResults);
		$this->assertEquals(0,$this->View()->sSearchResultsNum);
		$this->reset();
    }
	
}