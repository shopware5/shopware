<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5384
 */
class Shopware_RegressionTests_Ticket5384 extends Enlight_Components_Test_Controller_TestCase
{    
    /**
	 * Test case method
	 */
	public function testTagCloud()
    {
        //todo@hl: this don't works. Fix it please!
        $this->markTestIncomplete("Shopware_RegressionTests_Ticket5384 marked as incomplete!");
        return;


    	$this->dispatch('/');
    	if(!empty(Shopware()->Config()->TagCloudMax)) {
			$tagSize = (int) Shopware()->Config()->TagCloudMax;
    	} else {
			$tagSize = 50;
		}
		$this->assertArrayCount($tagSize, $this->View()->sCloud);
    }
}