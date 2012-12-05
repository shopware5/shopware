<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 3067
 */
class Shopware_RegressionTests_Ticket3067 extends Enlight_Components_Test_Controller_TestCase
{    
    /**
     * Test case method
     */
	public function testSearchOrder()
	{
		$this->dispatch('/search?sSearch=mode');
		$searchResults = $this->View()->sSearchResults;
		$this->reset();
		
		$this->dispatch('/search?sSearch=mode&sSort=6');
		$searchResults1 = $this->View()->sSearchResults;
		$this->reset();
		// todo@all Reactivate test
		//$this->assertNotEmpty($searchResults);
		//$this->assertEquals($searchResults['sArticles']['articleID'], $searchResults1['sArticles']['articleID']);
	}
}